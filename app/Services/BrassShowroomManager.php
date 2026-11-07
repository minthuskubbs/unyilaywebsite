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
                'room' => 'room',
            ];
            foreach ($map as $source => $target) {
                if (array_key_exists($source, $input)) {
                    $config['artworks'][$index][$target] = $input[$source];
                }
            }
            if (array_key_exists('notion_page_id', $input)) {
                if ($input['notion_page_id'] === null || $input['notion_page_id'] === '') {
                    unset($config['artworks'][$index]['notionPageId']);
                } else {
                    $config['artworks'][$index]['notionPageId'] = self::normalizeNotionId($input['notion_page_id']);
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
        [$path, $width, $height] = $this->storeImage($encoded, $filename, $id);

        try {
            return $this->mutate(function (array $config) use ($id, $path, $width, $height): array {
                $index = $this->indexOf($config, $id);
                $config['artworks'][$index]['image'] = Storage::url($path);
                $config['artworks'][$index]['crop'] = [0, 0, $width, $height];
                // A thumbnail made for the old image no longer matches.
                unset($config['artworks'][$index]['thumb']);

                return $config;
            }, $userId);
        } catch (\Throwable $error) {
            Storage::disk('public')->delete($path);
            throw $error;
        }
    }

    /**
     * Add a new artwork to the private draft. The image is either uploaded (base64) or an existing
     * file shipped under public/vendor/brass-showroom/. Silver-room artworks are hung automatically
     * by the frontend at their physical size; brass-room artworks need wall, slot and height.
     */
    public function addArtwork(array $input, int $userId): array
    {
        $stored = [];
        try {
            if (isset($input['image_base64'])) {
                [$path, $width, $height] = $stored[] = $this->storeImage($input['image_base64'], $input['filename'] ?? $input['id'], $input['id']);
                $image = Storage::url($path);
            } else {
                $image = $this->vendorPath($input['image_path'] ?? '', 'image_path');
                [$width, $height] = $this->dimensions($image, 'image_path');
            }
            $thumb = null;
            if (isset($input['thumbnail_base64'])) {
                $stored[] = $this->storeImage($input['thumbnail_base64'], ($input['filename'] ?? $input['id']).'-thumb', $input['id']);
                $thumb = Storage::url(end($stored)[0]);
            } elseif (isset($input['thumbnail_path'])) {
                $thumb = $this->vendorPath($input['thumbnail_path'], 'thumbnail_path');
                $this->dimensions($thumb, 'thumbnail_path');
            }

            $room = $input['room'] ?? 'silver';
            $artwork = [
                'id' => $input['id'],
                'room' => $room,
                'my' => $input['title_my'],
                'en' => $input['title_en'],
                'orientation' => $input['orientation'] ?? ($input['width_inches'] >= $input['height_inches'] ? 'landscape' : 'portrait'),
                'widthInches' => $input['width_inches'],
                'heightInches' => $input['height_inches'],
                'priceMmk' => $input['price_mmk'],
                'image' => $image,
                'crop' => [0, 0, $width, $height],
                'wall' => $room === 'silver' ? 'auto' : ($input['wall'] ?? 'back'),
                'slot' => $room === 'silver' ? 0 : ($input['slot'] ?? 0),
                'height' => $input['height'] ?? 1.5,
                'scale' => $input['scale'] ?? 1,
                'lightIntensity' => $input['light_intensity'] ?? ($room === 'silver' ? 6 : 8),
            ];
            if ($thumb !== null) {
                $artwork['thumb'] = $thumb;
            }
            if (! empty($input['notion_page_id'])) {
                $artwork['notionPageId'] = self::normalizeNotionId($input['notion_page_id']);
            }

            return $this->mutate(function (array $config) use ($artwork, $input): array {
                if (collect($config['artworks'])->contains(fn (array $a): bool => $a['id'] === $artwork['id'])) {
                    throw ValidationException::withMessages(['id' => "Artwork id already exists: {$artwork['id']}"]);
                }
                $position = $input['position'] ?? count($config['artworks']);
                array_splice($config['artworks'], max(0, min($position, count($config['artworks']))), 0, [$artwork]);

                return $config;
            }, $userId);
        } catch (\Throwable $error) {
            foreach ($stored as [$path]) {
                Storage::disk('public')->delete($path);
            }
            throw $error;
        }
    }

    public function removeArtwork(string $id, int $userId): array
    {
        return $this->mutate(function (array $config) use ($id): array {
            array_splice($config['artworks'], $this->indexOf($config, $id), 1);

            return $config;
        }, $userId);
    }

    private function indexOf(array $config, string $id): int
    {
        $index = collect($config['artworks'])->search(fn (array $artwork): bool => $artwork['id'] === $id);
        if ($index === false) {
            throw ValidationException::withMessages(['id' => "Unknown artwork id: {$id}"]);
        }

        return $index;
    }

    /** Decode, validate and store an uploaded image. Returns [storage path, width, height]. */
    private function storeImage(string $encoded, string $filename, string $fallbackName): array
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

        $safeName = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: $fallbackName;
        $path = 'brass-showroom/'.now()->format('YmdHis').'-'.Str::random(12).'-'.$safeName.'.'.$allowed[$mime];
        Storage::disk('public')->put($path, $binary, ['visibility' => 'public']);

        return [$path, $width, $height];
    }

    /** Accept only image files that ship with the package under /vendor/brass-showroom/. */
    private function vendorPath(string $path, string $field): string
    {
        $path = '/'.ltrim($path, '/');
        if (! preg_match('#^/vendor/brass-showroom/[A-Za-z0-9/_.-]+\.(png|jpe?g|webp)$#', $path) || str_contains($path, '..')) {
            throw ValidationException::withMessages([$field => 'Use an image under /vendor/brass-showroom/.']);
        }

        return $path;
    }

    private function dimensions(string $publicPath, string $field): array
    {
        $file = public_path(ltrim($publicPath, '/'));
        $size = is_file($file) ? @getimagesize($file) : false;
        if ($size === false || ! in_array($size['mime'] ?? '', ['image/png', 'image/jpeg', 'image/webp'], true)) {
            throw ValidationException::withMessages([$field => 'Image file not found or not a PNG, JPEG, or WebP image.']);
        }

        return [$size[0], $size[1]];
    }

    /** Notion page ids are stored in the dashed, lower-case form the Notion API returns. */
    public static function normalizeNotionId(string $id): string
    {
        $hex = strtolower(str_replace('-', '', $id));

        return substr($hex, 0, 8).'-'.substr($hex, 8, 4).'-'.substr($hex, 12, 4).'-'.substr($hex, 16, 4).'-'.substr($hex, 20);
    }

    /**
     * Apply prices read from Notion ([notion page id => price in MMK]) to the live showroom.
     *
     * Only priceMmk changes. The published config gets a new revision straight away, and the same
     * prices are written into the draft so unpublished edits are kept (never published by the sync)
     * and a later publish does not bring old prices back. An artwork is linked by its notionPageId,
     * or, when it has none, by the notionPageId of the artwork with the same id in default.json.
     *
     * Returns the list of changes: [id, title, from, to].
     */
    public function syncPricesFromNotion(array $prices, bool $dryRun = false): array
    {
        $fallback = collect($this->defaults()['artworks'] ?? [])
            ->filter(fn (array $a): bool => isset($a['notionPageId']))
            ->mapWithKeys(fn (array $a): array => [$a['id'] => $a['notionPageId']]);
        $apply = function (array $config) use ($prices, $fallback): array {
            $changes = [];
            foreach ($config['artworks'] as $i => $artwork) {
                $page = $artwork['notionPageId'] ?? $fallback[$artwork['id']] ?? null;
                if ($page === null || ! array_key_exists($page, $prices)) {
                    continue;
                }
                $price = $prices[$page];
                if ($price !== (int) $artwork['priceMmk']) {
                    $changes[] = ['id' => $artwork['id'], 'title' => $artwork['en'], 'from' => (int) $artwork['priceMmk'], 'to' => $price];
                    $config['artworks'][$i]['priceMmk'] = $price;
                }
            }

            return [$config, $changes];
        };

        $state = $this->state();
        [, $changes] = $apply($state->published_config);
        if ($dryRun || $changes === []) {
            return $changes;
        }

        return DB::transaction(function () use ($apply): array {
            $state = BrassShowroom::query()->whereKey($this->state()->getKey())->lockForUpdate()->firstOrFail();
            [$published, $changes] = $apply($state->published_config);
            if ($changes === []) {
                return [];
            }
            $this->validateDocument($published);
            [$draft, $draftChanges] = $apply($state->draft_config);
            $this->validateDocument($draft);
            $revision = $state->published_revision + 1;
            $state->forceFill([
                'published_config' => $published,
                'published_revision' => $revision,
                'published_at' => now(),
            ] + ($draftChanges === [] ? [] : [
                'draft_config' => $draft,
                'draft_revision' => $state->draft_revision + 1,
                'draft_updated_at' => now(),
            ]))->save();
            BrassShowroomRevision::query()->create([
                'brass_showroom_id' => $state->id,
                'revision' => $revision,
                'config' => $published,
                'note' => 'Notion price sync: '.count($changes).' price(s) changed',
                'published_by' => null,
                'created_at' => now(),
            ]);

            return $changes;
        });
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

    /** Replace the private draft with the prepared catalogue in default.json. The live showroom is untouched until publish. */
    public function importCatalogueToDraft(int $userId): array
    {
        return $this->mutate(fn (array $config): array => $this->defaults(), $userId);
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
            'artworks' => ['required', 'array', 'min:1', 'max:'.(int) config('brass-showroom.max_artworks', 250)],
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
            'artworks.*.room' => ['sometimes', 'in:brass,silver'],
            'artworks.*.thumb' => ['sometimes', 'string', 'starts_with:/vendor/,/storage/', 'max:255'],
            'artworks.*.wall' => ['required', 'in:left,right,back,front,auto'],
            'artworks.*.slot' => ['required', 'numeric', 'between:-3,3'],
            'artworks.*.height' => ['required', 'numeric', 'between:0.8,2.2'],
            'artworks.*.scale' => ['required', 'numeric', 'between:0.5,1.6'],
            'artworks.*.lightIntensity' => ['required', 'numeric', 'between:0,15'],
            'artworks.*.notionPageId' => ['sometimes', 'string', 'regex:/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/'],
        ])->validate();

        foreach ($config['artworks'] as $index => $artwork) {
            $room = $artwork['room'] ?? 'brass';
            if ($room === 'brass' && $artwork['wall'] === 'auto') {
                throw ValidationException::withMessages(["artworks.{$index}.wall" => 'Brass-room artworks need a left, right, back, or front wall.']);
            }
            if ($room === 'silver' && $artwork['wall'] !== 'auto') {
                throw ValidationException::withMessages(["artworks.{$index}.wall" => 'Silver-room artworks are hung automatically; use wall "auto".']);
            }
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
