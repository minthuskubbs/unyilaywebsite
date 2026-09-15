<?php

namespace App\Mcp;

use App\Services\BrassShowroomFrontendReleaseManager;
use App\Services\BrassShowroomManager;
use App\Support\BrassShowroomAuthorization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class BrassShowroomToolRegistry
{
    public function __construct(
        private readonly BrassShowroomManager $showroom,
        private readonly BrassShowroomFrontendReleaseManager $releases,
    ) {}

    public function definitions(?Authenticatable $user): array
    {
        $this->userId($user);
        $tools = [
            $this->definition('get_showroom_state', 'Read the complete draft or published showroom configuration.', ['view' => $this->string(['draft', 'published'])]),
            $this->definition('update_artwork_draft', 'Update artwork titles, price, framed size, placement, light, or crop in the private draft.', $this->artworkProperties(), ['id']),
            $this->definition('replace_artwork_image_draft', 'Replace one draft artwork image with validated PNG, JPEG, or WebP base64 bytes.', [
                'id' => $this->string(), 'filename' => $this->string(), 'image_base64' => $this->string(),
            ], ['id', 'filename', 'image_base64']),
            $this->definition('update_showroom_layout_draft', 'Update private room colors, lighting, exposure, and bench layout.', $this->layoutProperties()),
            $this->definition('create_showroom_preview', 'Create a short-lived signed preview URL for the private draft.'),
            $this->definition('publish_showroom', 'Publish the reviewed draft using its exact revision to prevent concurrent overwrite.', [
                'expected_draft_revision' => $this->integer(), 'note' => $this->string(),
            ], ['expected_draft_revision']),
            $this->definition('list_showroom_revisions', 'List the latest 20 published showroom revisions.'),
            $this->definition('restore_showroom_revision_to_draft', 'Copy a published revision into the private draft without publishing it.', ['revision' => $this->integer()], ['revision']),
        ];

        if (BrassShowroomAuthorization::allowsFrontendDeploy($user)) {
            $tools = array_merge($tools, [
                $this->definition('get_frontend_release_status', 'Read the active frontend release and recent staged releases.'),
                $this->definition('stage_frontend_release', 'Stage immutable verified JavaScript and CSS bundles in private storage.', [
                    'version' => $this->string(), 'javascript_base64' => $this->string(), 'stylesheet_base64' => $this->string(),
                    'javascript_sha256' => $this->string(), 'stylesheet_sha256' => $this->string(), 'note' => $this->string(),
                ], ['version', 'javascript_base64', 'stylesheet_base64', 'javascript_sha256', 'stylesheet_sha256']),
                $this->definition('create_frontend_release_preview', 'Create a signed preview URL for a staged frontend release.', ['release_id' => $this->string()], ['release_id']),
                $this->definition('activate_frontend_release', 'Atomically activate a reviewed release or roll back to a previous release.', [
                    'release_id' => $this->string(), 'expected_active_release_id' => $this->string(), 'note' => $this->string(),
                ], ['release_id', 'expected_active_release_id']),
            ]);
        }

        return $tools;
    }

    public function call(string $name, array $arguments, ?Authenticatable $user): array
    {
        $userId = $this->userId($user);
        $payload = match ($name) {
            'get_showroom_state' => $this->getState($arguments),
            'update_artwork_draft' => $this->updateArtwork($arguments, $userId),
            'replace_artwork_image_draft' => $this->replaceImage($arguments, $userId),
            'update_showroom_layout_draft' => $this->updateLayout($arguments, $userId),
            'create_showroom_preview' => $this->showroomPreview($arguments),
            'publish_showroom' => $this->publish($arguments, $userId),
            'list_showroom_revisions' => $this->noArguments($arguments, ['revisions' => $this->showroom->revisions()]),
            'restore_showroom_revision_to_draft' => $this->restore($arguments, $userId),
            'get_frontend_release_status' => $this->frontend($user, false, fn () => $this->noArguments($arguments, $this->releases->status())),
            'stage_frontend_release' => $this->frontend($user, true, fn () => $this->stage($arguments, $userId)),
            'create_frontend_release_preview' => $this->frontend($user, false, fn () => $this->frontendPreview($arguments)),
            'activate_frontend_release' => $this->frontend($user, true, fn () => $this->activate($arguments, $userId)),
            default => throw new \BadMethodCallException('Unknown tool.'),
        };

        return ['content' => [['type' => 'text', 'text' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)]], 'isError' => false];
    }

    private function getState(array $input): array
    {
        $input = validator($input, ['view' => ['nullable', 'in:draft,published']])->validate();
        return $this->showroom->view($input['view'] ?? 'draft');
    }

    private function updateArtwork(array $input, int $userId): array
    {
        $input = validator($input, [
            'id' => ['required', 'alpha_dash:ascii', 'max:40'], 'title_my' => ['sometimes', 'string', 'max:120'],
            'title_en' => ['sometimes', 'string', 'max:120'], 'price_mmk' => ['sometimes', 'integer', 'between:0,1000000000'],
            'width_inches' => ['sometimes', 'numeric', 'between:4,80'], 'height_inches' => ['sometimes', 'numeric', 'between:4,80'],
            'orientation' => ['sometimes', 'in:landscape,portrait'], 'wall' => ['sometimes', 'in:left,right,back'],
            'slot' => ['sometimes', 'numeric', 'between:-1.5,1.5'], 'height' => ['sometimes', 'numeric', 'between:0.8,2.2'],
            'scale' => ['sometimes', 'numeric', 'between:0.5,1.6'], 'light_intensity' => ['sometimes', 'numeric', 'between:0,15'],
            'crop_x' => ['sometimes', 'integer', 'min:0', 'max:20000', 'required_with:crop_y,crop_width,crop_height'],
            'crop_y' => ['sometimes', 'integer', 'min:0', 'max:20000', 'required_with:crop_x,crop_width,crop_height'],
            'crop_width' => ['sometimes', 'integer', 'between:1,20000', 'required_with:crop_x,crop_y,crop_height'],
            'crop_height' => ['sometimes', 'integer', 'between:1,20000', 'required_with:crop_x,crop_y,crop_width'],
        ])->validate();
        $id = $input['id']; unset($input['id']);
        return $this->showroom->updateArtwork($id, $input, $userId);
    }

    private function replaceImage(array $input, int $userId): array
    {
        $input = validator($input, ['id' => ['required', 'alpha_dash:ascii', 'max:40'], 'filename' => ['required', 'string', 'max:120'], 'image_base64' => ['required', 'string']])->validate();
        return $this->showroom->replaceImage($input['id'], $input['image_base64'], $input['filename'], $userId);
    }

    private function updateLayout(array $input, int $userId): array
    {
        $input = validator($input, [
            'wall_color' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'], 'floor_color' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'ceiling_color' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'], 'spotlight_color' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'ambient_intensity' => ['sometimes', 'numeric', 'between:0,3'], 'hemisphere_intensity' => ['sometimes', 'numeric', 'between:0,4'],
            'bounce_intensity' => ['sometimes', 'numeric', 'between:0,12'], 'tone_mapping_exposure' => ['sometimes', 'numeric', 'between:0.5,2'],
            'bench_visible' => ['sometimes', 'boolean'], 'bench_x' => ['sometimes', 'numeric', 'between:-1,1'], 'bench_z' => ['sometimes', 'numeric', 'between:-1,1'],
        ])->validate();
        return $this->showroom->updateLayout($input, $userId);
    }

    private function showroomPreview(array $input): array
    {
        $this->noArguments($input, []); $state = $this->showroom->view('draft'); $minutes = (int) config('brass-showroom.preview_minutes', 20);
        return ['preview_url' => URL::temporarySignedRoute('brass-showroom.preview', now()->addMinutes($minutes)), 'expires_in_minutes' => $minutes, 'draft_revision' => $state['draft_revision']];
    }

    private function publish(array $input, int $userId): array
    {
        $input = validator($input, ['expected_draft_revision' => ['required', 'integer', 'min:1'], 'note' => ['nullable', 'string', 'max:240']])->validate();
        return $this->showroom->publish($input['expected_draft_revision'], $input['note'] ?? null, $userId);
    }

    private function restore(array $input, int $userId): array
    {
        $input = validator($input, ['revision' => ['required', 'integer', 'min:1']])->validate();
        return $this->showroom->restoreToDraft($input['revision'], $userId);
    }

    private function stage(array $input, int $userId): array
    {
        $input = validator($input, [
            'version' => ['required', 'string', 'regex:/^[0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?$/', 'max:40'],
            'javascript_base64' => ['required', 'string'], 'stylesheet_base64' => ['required', 'string'],
            'javascript_sha256' => ['required', 'string', 'regex:/^[a-fA-F0-9]{64}$/'], 'stylesheet_sha256' => ['required', 'string', 'regex:/^[a-fA-F0-9]{64}$/'],
            'note' => ['nullable', 'string', 'max:240'],
        ])->validate();
        return $this->releases->stage($input, $userId);
    }

    private function frontendPreview(array $input): array
    {
        $input = validator($input, ['release_id' => ['required', 'uuid']])->validate(); $release = $this->releases->metadata($input['release_id']); $minutes = (int) config('brass-showroom.preview_minutes', 20);
        return ['preview_url' => URL::temporarySignedRoute('brass-showroom.frontend-preview', now()->addMinutes($minutes), ['release' => $release['id']]), 'expires_in_minutes' => $minutes, 'release' => $release];
    }

    private function activate(array $input, int $userId): array
    {
        $input = validator($input, ['release_id' => ['required', 'string', 'max:40'], 'expected_active_release_id' => ['required', 'string', 'max:40'], 'note' => ['nullable', 'string', 'max:240']])->validate();
        foreach (['release_id', 'expected_active_release_id'] as $field) if ($input[$field] !== 'bundled' && ! Str::isUuid($input[$field])) throw ValidationException::withMessages([$field => 'Release id must be bundled or a UUID.']);
        return $this->releases->activate($input['release_id'], $input['expected_active_release_id'], $input['note'] ?? null, $userId);
    }

    private function frontend(?Authenticatable $user, bool $write, callable $callback): array
    {
        if (! BrassShowroomAuthorization::allowsFrontendDeploy($user)) throw new AuthorizationException('Frontend deployment is disabled or this token lacks showroom:deploy.');
        if ($write) { $key = 'brass-showroom-frontend-deploy:'.$user->getAuthIdentifier(); $limit = (int) config('brass-showroom.frontend_deploys_per_hour', 6); if (RateLimiter::tooManyAttempts($key, $limit)) throw ValidationException::withMessages(['rate_limit' => 'Frontend deployment rate limit reached.']); RateLimiter::hit($key, 3600); }
        return $callback();
    }

    private function userId(?Authenticatable $user): int
    {
        if (! BrassShowroomAuthorization::allows($user)) throw new AuthorizationException('You are not allowed to manage the brass showroom.');
        return (int) $user->getAuthIdentifier();
    }

    private function noArguments(array $input, array $result): array { validator($input, ['*' => ['prohibited']])->validate(); return $result; }
    private function definition(string $name, string $description, array $properties = [], array $required = []): array { $schema = ['type' => 'object', 'properties' => (object) $properties, 'additionalProperties' => false]; if ($required) $schema['required'] = $required; return ['name' => $name, 'description' => $description, 'inputSchema' => $schema]; }
    private function string(?array $enum = null): array { $schema = ['type' => 'string']; if ($enum) $schema['enum'] = $enum; return $schema; }
    private function integer(): array { return ['type' => 'integer']; }
    private function number(): array { return ['type' => 'number']; }
    private function boolean(): array { return ['type' => 'boolean']; }
    private function artworkProperties(): array { return ['id'=>$this->string(),'title_my'=>$this->string(),'title_en'=>$this->string(),'price_mmk'=>$this->integer(),'width_inches'=>$this->number(),'height_inches'=>$this->number(),'orientation'=>$this->string(['landscape','portrait']),'wall'=>$this->string(['left','right','back']),'slot'=>$this->number(),'height'=>$this->number(),'scale'=>$this->number(),'light_intensity'=>$this->number(),'crop_x'=>$this->integer(),'crop_y'=>$this->integer(),'crop_width'=>$this->integer(),'crop_height'=>$this->integer()]; }
    private function layoutProperties(): array { return ['wall_color'=>$this->string(),'floor_color'=>$this->string(),'ceiling_color'=>$this->string(),'spotlight_color'=>$this->string(),'ambient_intensity'=>$this->number(),'hemisphere_intensity'=>$this->number(),'bounce_intensity'=>$this->number(),'tone_mapping_exposure'=>$this->number(),'bench_visible'=>$this->boolean(),'bench_x'=>$this->number(),'bench_z'=>$this->number()]; }
}
