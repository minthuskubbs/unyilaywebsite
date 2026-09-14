<?php

namespace App\Services;

use App\Models\BrassShowroom;
use App\Models\BrassShowroomRevision;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class BrassShowroomManager
{
    public function state(): BrassShowroom
    {
        $defaults = $this->defaults();

        $state = BrassShowroom::query()->firstOrCreate(
            ['key' => config('brass-showroom.key', 'main')],
            ['published_config' => $defaults, 'draft_config' => $defaults, 'published_revision' => 1, 'draft_revision' => 1]
        );
        $state->revisions()->firstOrCreate(
            ['revision' => 1],
            ['config' => $state->published_config, 'note' => 'Initial showroom configuration', 'created_at' => now()]
        );

        return $state;
    }

    public function defaults(): array
    {
        return json_decode(file_get_contents(resource_path('showroom/default.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function view(string $view = 'draft'): array
    {
        $state = $this->state();
        $config = $view === 'published' ? $state->published_config : $state->draft_config;

        return [
            'view' => $view,
            'published_revision' => $state->published_revision,
            'draft_revision' => $state->draft_revision,
            'has_unpublished_changes' => $state->draft_config !== $state->published_config,
            'config' => $config,
        ];
    }

    public function updateArtwork(string $id, array $input, int $userId): array
    {
        return $this->mutate(function (array $config) use ($id, $input): array {
            $index = collect($config['artworks'])->search(fn (array $artwork): bool => $artwork['id'] === $id);
            if ($index === false) {
                throw ValidationException::withMessages(['id' => "Unknown artwork id: {$id}"]);
            }

            $map = [
                'title_my' => 'my', 'title_en' => 'en', 'price_mmk' => 'priceMmk',
                'width_inches' => 'widthInches', 'height_inches' => 'heightInches',
                'orientation' => 'orientation', 'wall' => 'wall', 'slot' => 'slot',
                'height' => 'height', 'scale' => 'scale', 'light_intensity' => 'lightIntensity',
            ];
            foreach ($map as $source => $target) {
                if (array_key_exists($source, $input)) {
                    $config['artworks'][$index][$target] = $input[$source];
                }
            }
            if (array_key_exists('crop_x', $input)) {
                $config['artworks'][$index]['crop'] = [
                    $input['crop_x'], $input['crop_y'], $input['crop_width'], $input['crop_height'],
                ];
            }

            return $config;
        }, $userId);
    }

    public function updateLayout(array $input, int $userId): array
    {
        return $this->mutate(function (array $config) use ($input): array {
            $themeMap = [
                'wall_color' => 'wallColor', 'floor_color' => 'floorColor',
                'ceiling_color' => 'ceilingColor', 'ambient_intensity' => 'ambientIntensity',
                'hemisphere_intensity' => 'hemisphereIntensity', 'bounce_intensity' => 'bounceIntensity',
                'spotlight_color' => 'spotlightColor', 'tone_mapping_exposure' => 'toneMappingExposure',
            ];
            foreach ($themeMap as $source => $target) {
                if (array_key_exists($source, $input)) {
                    $config['theme'][$target] = $input[$source];
                }
            }
            foreach (['bench_visible' => 'visible', 'bench_x' => 'x', 'bench_z' => 'z'] as $source => $target) {
                if (array_key_exists($source, $input)) {
                    $config['bench'][$target] = $input[$source];
                }
            }

            return $config;
        }, $userId);
    }

    public function replaceImage(string $id, string $encoded, string $filename, int $userId): array
    {
        $payload = preg_replace('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', '', $encoded);
        $binary = base64_decode($payload, true);
        if ($binary === false) {
            throw ValidationException::withMessages(['image_base64' => 'The image is not valid base64.']);
        }
        if (strlen($binary) > config('brass-showroom.max_image_bytes', 8 * 1024 * 1024)) {
            throw ValidationException::withMessages(['image_base64' => 'The image exceeds the configured size limit.']);
        }

        $info = @getimagesizefromstring($binary);
        $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
        $mime = $info['mime'] ?? '';
        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary);
        if (! isset($allowed[$mime]) || $detectedMime !== $mime) {
            throw ValidationException::withMessages(['image_base64' => 'Only decoded PNG, JPEG, and WebP images are accepted.']);
        }
        [$width, $height] = $info;
        if ($width < 256 || $height < 256 || ($width * $height) > config('brass-showroom.max_image_pixels', 20_000_000)) {
            throw ValidationException::withMessages(['image_base64' => 'Image dimensions are outside the allowed range.']);
        }

        $safeName = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: $id;
        $path = 'brass-showroom/'.now()->format('YmdHis').'-'.Str::random(12).'-'.$safeName.'.'.$allowed[$mime];
        Storage::disk('public')->put($path, $binary, ['visibility' => 'public']);

        try {
            return $this->mutate(function (array $config) use ($id, $path, $width, $height): array {
                $index = collect($config['artworks'])->search(fn (array $artwork): bool => $artwork['id'] === $id);
                if ($index === false) {
                    throw ValidationException::withMessages(['id' => "Unknown artwork id: {$id}"]);
                }
                $config['artworks'][$index]['image'] = Storage::url($path);
                $config['artworks'][$index]['crop'] = [0, 0, $width, $height];

                return $config;
            }, $userId);
        } catch (\Throwable $error) {
            Storage::disk('public')->delete($path);
            throw $error;
        }
    }

    public function publish(int $expectedDraftRevision, ?string $note, int $userId): array
    {
        return DB::transaction(function () use ($expectedDraftRevision, $note, $userId): array {
            $state = BrassShowroom::query()->whereKey($this->state()->getKey())->lockForUpdate()->firstOrFail();
            if ($state->draft_revision !== $expectedDraftRevision) {
                throw ValidationException::withMessages(['expected_draft_revision' => 'Draft changed after review. Read it again before publishing.']);
            }
            $this->validateDocument($state->draft_config);
            if ($state->draft_config === $state->published_config) {
                return $this->view('published');
            }
            $revision = $state->published_revision + 1;
            $state->forceFill([
                'published_config' => $state->draft_config,
                'published_revision' => $revision,
                'published_at' => now(),
                'updated_by' => $userId,
            ])->save();
            BrassShowroomRevision::query()->create([
                'brass_showroom_id' => $state->id,
                'revision' => $revision,
                'config' => $state->published_config,
                'note' => $note,
                'published_by' => $userId,
                'created_at' => now(),
            ]);

            return $this->view('published');
        });
    }

    public function restoreToDraft(int $revision, int $userId): array
    {
        $state = $this->state();
        $snapshot = $state->revisions()->where('revision', $revision)->firstOrFail();

        return $this->mutate(fn (array $config): array => $snapshot->config, $userId);
    }

    public function revisions(): array
    {
        return $this->state()->revisions()->latest('revision')->limit(20)
            ->get(['revision', 'note', 'published_by', 'created_at'])->toArray();
    }

    private function mutate(callable $callback, int $userId): array
    {
        return DB::transaction(function () use ($callback, $userId): array {
            $state = BrassShowroom::query()->whereKey($this->state()->getKey())->lockForUpdate()->firstOrFail();
            $next = $callback($state->draft_config);
            $this->validateDocument($next);
            $state->forceFill([
                'draft_config' => $next,
                'draft_revision' => $state->draft_revision + 1,
                'draft_updated_at' => now(),
                'updated_by' => $userId,
            ])->save();

            return $this->view('draft');
        });
    }

    private function validateDocument(array $config): void
    {
        validator($config, [
            'version' => ['required', 'integer', 'in:1'],
            'theme.wallColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.floorColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.ceilingColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.spotlightColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.ambientIntensity' => ['required', 'numeric', 'between:0,3'],
            'theme.hemisphereIntensity' => ['required', 'numeric', 'between:0,4'],
            'theme.bounceIntensity' => ['required', 'numeric', 'between:0,12'],
            'theme.toneMappingExposure' => ['required', 'numeric', 'between:0.5,2'],
            'bench.visible' => ['required', 'boolean'],
            'bench.x' => ['required', 'numeric', 'between:-1,1'],
            'bench.z' => ['required', 'numeric', 'between:-1,1'],
            'artworks' => ['required', 'array', 'min:1', 'max:12'],
            'artworks.*.id' => ['required', 'alpha_dash:ascii', 'max:40', 'distinct'],
            'artworks.*.my' => ['required', 'string', 'max:120'],
            'artworks.*.en' => ['required', 'string', 'max:120'],
            'artworks.*.orientation' => ['required', 'in:landscape,portrait'],
            'artworks.*.widthInches' => ['required', 'numeric', 'between:4,80'],
            'artworks.*.heightInches' => ['required', 'numeric', 'between:4,80'],
            'artworks.*.priceMmk' => ['required', 'integer', 'between:0,1000000000'],
            'artworks.*.image' => ['required', 'string', 'starts_with:/vendor/,/storage/', 'max:255'],
            'artworks.*.crop' => ['required', 'array', 'size:4'],
            'artworks.*.crop.*' => ['required', 'integer', 'min:0', 'max:20000'],
            'artworks.*.wall' => ['required', 'in:left,right,back'],
            'artworks.*.slot' => ['required', 'numeric', 'between:-1.5,1.5'],
            'artworks.*.height' => ['required', 'numeric', 'between:0.8,2.2'],
            'artworks.*.scale' => ['required', 'numeric', 'between:0.5,1.6'],
            'artworks.*.lightIntensity' => ['required', 'numeric', 'between:0,15'],
        ])->validate();

        foreach ($config['artworks'] as $index => $artwork) {
            if ($artwork['crop'][2] < 1 || $artwork['crop'][3] < 1) {
                throw ValidationException::withMessages(["artworks.{$index}.crop" => 'Crop width and height must be positive.']);
            }
            $relative = ltrim($artwork['image'], '/');
            $file = str_starts_with($relative, 'storage/')
                ? storage_path('app/public/'.substr($relative, strlen('storage/')))
                : public_path($relative);
            $size = is_file($file) ? @getimagesize($file) : false;
            if ($size !== false) {
                [$x, $y, $width, $height] = $artwork['crop'];
                if (($x + $width) > $size[0] || ($y + $height) > $size[1]) {
                    throw ValidationException::withMessages(["artworks.{$index}.crop" => 'Crop rectangle exceeds the image dimensions.']);
                }
            }
        }
    }
}
