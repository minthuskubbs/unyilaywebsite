<?php

namespace App\Services;

use App\Models\BrassShowroom;
use App\Models\BrassShowroomFrontendRelease;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class BrassShowroomFrontendReleaseManager
{
    public function status(): array
    {
        $state = app(BrassShowroomManager::class)->state();
        $activeId = $state->frontend_release_id ?: 'bundled';

        return [
            'deploy_enabled' => (bool) config('brass-showroom.frontend_deploy_enabled', false),
            'active_release_id' => $activeId,
            'active_release' => $this->metadata($activeId),
            'recent_releases' => BrassShowroomFrontendRelease::query()->latest()->limit(20)->get()->map(fn ($release) => $this->modelMetadata($release))->all(),
            'recent_activations' => DB::table('brass_showroom_frontend_activations')->latest('id')->limit(20)->get()->map(fn ($activation) => (array) $activation)->all(),
        ];
    }

    public function stage(array $input, int $userId): array
    {
        $javascript = $this->decode($input['javascript_base64'], 'javascript_base64');
        $stylesheet = $this->decode($input['stylesheet_base64'], 'stylesheet_base64');
        $this->validateBundle($javascript, $stylesheet);
        $this->assertHash($javascript, $input['javascript_sha256'], 'javascript_sha256');
        $this->assertHash($stylesheet, $input['stylesheet_sha256'], 'stylesheet_sha256');

        $id = (string) Str::uuid();
        $directory = "brass-showroom/frontend/{$id}";
        $javascriptPath = "{$directory}/brass-showroom.js";
        $stylesheetPath = "{$directory}/brass-showroom.css";
        $disk = Storage::disk('local');
        $disk->put($javascriptPath, $javascript);
        $disk->put($stylesheetPath, $stylesheet);

        try {
            $release = BrassShowroomFrontendRelease::query()->create([
                'id' => $id,
                'version' => $input['version'],
                'javascript_path' => $javascriptPath,
                'stylesheet_path' => $stylesheetPath,
                'javascript_sha256' => hash('sha256', $javascript),
                'stylesheet_sha256' => hash('sha256', $stylesheet),
                'javascript_bytes' => strlen($javascript),
                'stylesheet_bytes' => strlen($stylesheet),
                'note' => $input['note'] ?? null,
                'created_by' => $userId,
            ]);
        } catch (\Throwable $error) {
            $disk->deleteDirectory($directory);
            throw $error;
        }

        return ['staged_release' => $this->modelMetadata($release), 'active_release_id' => $this->activeId()];
    }

    public function activate(string $releaseId, string $expectedActiveReleaseId, ?string $note, int $userId): array
    {
        return DB::transaction(function () use ($releaseId, $expectedActiveReleaseId, $note, $userId): array {
            $state = BrassShowroom::query()->whereKey(app(BrassShowroomManager::class)->state()->getKey())->lockForUpdate()->firstOrFail();
            $current = $state->frontend_release_id ?: 'bundled';
            if (! hash_equals($current, $expectedActiveReleaseId)) {
                throw ValidationException::withMessages(['expected_active_release_id' => 'The active frontend release changed after review. Read status again before activating.']);
            }
            if (hash_equals($current, $releaseId)) {
                return $this->status();
            }
            if ($releaseId === 'bundled') {
                $state->frontend_release_id = null;
            } else {
                $release = BrassShowroomFrontendRelease::query()->whereKey($releaseId)->lockForUpdate()->firstOrFail();
                $this->verifyStoredRelease($release);
                $release->forceFill([
                    'note' => $note ?: $release->note,
                    'activated_by' => $userId,
                    'activated_at' => now(),
                ])->save();
                $state->frontend_release_id = $release->id;
            }
            $state->updated_by = $userId;
            $state->save();
            DB::table('brass_showroom_frontend_activations')->insert([
                'from_release_id' => $current,
                'to_release_id' => $releaseId,
                'note' => $note,
                'activated_by' => $userId,
                'created_at' => now(),
            ]);

            return $this->status();
        });
    }

    public function activeId(): string
    {
        return app(BrassShowroomManager::class)->state()->frontend_release_id ?: 'bundled';
    }

    public function asset(string $releaseId, string $type, bool $requireActive = false): array
    {
        if (! in_array($type, ['javascript', 'stylesheet'], true)) {
            throw ValidationException::withMessages(['asset' => 'Asset must be javascript or stylesheet.']);
        }
        if ($requireActive && ! hash_equals($this->activeId(), $releaseId)) {
            abort(404);
        }
        if ($releaseId === 'bundled') {
            $file = public_path('vendor/brass-showroom/brass-showroom.'.($type === 'javascript' ? 'js' : 'css'));
            abort_unless(is_file($file), 404);
            $bytes = file_get_contents($file);
        } else {
            $release = BrassShowroomFrontendRelease::query()->findOrFail($releaseId);
            $this->verifyStoredRelease($release);
            $path = $type === 'javascript' ? $release->javascript_path : $release->stylesheet_path;
            $bytes = Storage::disk('local')->get($path);
        }

        return [
            'bytes' => $bytes,
            'sha256' => hash('sha256', $bytes),
            'content_type' => $type === 'javascript' ? 'application/javascript; charset=UTF-8' : 'text/css; charset=UTF-8',
        ];
    }

    public function metadata(string $releaseId): array
    {
        if ($releaseId === 'bundled') {
            $javascript = $this->asset('bundled', 'javascript');
            $stylesheet = $this->asset('bundled', 'stylesheet');
            return [
                'id' => 'bundled', 'version' => 'bundled', 'note' => 'Files deployed with the Laravel application',
                'javascript_sha256' => $javascript['sha256'], 'stylesheet_sha256' => $stylesheet['sha256'],
                'javascript_bytes' => strlen($javascript['bytes']), 'stylesheet_bytes' => strlen($stylesheet['bytes']),
                'created_at' => null, 'activated_at' => null,
            ];
        }

        return $this->modelMetadata(BrassShowroomFrontendRelease::query()->findOrFail($releaseId));
    }

    private function decode(string $encoded, string $field): string
    {
        $payload = preg_replace('/^data:[a-zA-Z0-9.+-]+\/[a-zA-Z0-9.+-]+;base64,/', '', $encoded);
        $bytes = base64_decode($payload, true);
        if ($bytes === false || $bytes === '') {
            throw ValidationException::withMessages([$field => 'The asset is not valid non-empty base64.']);
        }
        if (preg_match('//u', $bytes) !== 1 || str_contains($bytes, "\0")) {
            throw ValidationException::withMessages([$field => 'The asset must be UTF-8 text without null bytes.']);
        }
        return $bytes;
    }

    private function validateBundle(string $javascript, string $stylesheet): void
    {
        if (strlen($javascript) > config('brass-showroom.max_frontend_javascript_bytes', 2 * 1024 * 1024)) {
            throw ValidationException::withMessages(['javascript_base64' => 'JavaScript exceeds the configured size limit.']);
        }
        if (strlen($stylesheet) > config('brass-showroom.max_frontend_stylesheet_bytes', 256 * 1024)) {
            throw ValidationException::withMessages(['stylesheet_base64' => 'Stylesheet exceeds the configured size limit.']);
        }
        foreach (['eval(', 'new Function', 'document.write', 'innerHTML', 'outerHTML', 'insertAdjacentHTML', 'document.cookie', 'localStorage', 'sessionStorage', 'sendBeacon', 'WebSocket(', 'XMLHttpRequest'] as $forbidden) {
            if (str_contains($javascript, $forbidden)) {
                throw ValidationException::withMessages(['javascript_base64' => "Forbidden browser primitive: {$forbidden}"]);
            }
        }
        if (! str_contains($javascript, "new URL(") || ! str_contains($javascript, 'window.location.origin')) {
            throw ValidationException::withMessages(['javascript_base64' => 'The bundle must retain same-origin URL checks.']);
        }
        if (preg_match('/@import\b|expression\s*\(|behavior\s*:|javascript\s*:|url\s*\(\s*["\']?\s*(?:https?:)?\/\//i', $stylesheet)) {
            throw ValidationException::withMessages(['stylesheet_base64' => 'Stylesheet imports, active content, and external URLs are not allowed.']);
        }
        if (! str_contains($stylesheet, '.bs-root')) {
            throw ValidationException::withMessages(['stylesheet_base64' => 'Stylesheet must remain scoped to .bs-root.']);
        }
    }

    private function assertHash(string $bytes, string $expected, string $field): void
    {
        if (! hash_equals(strtolower($expected), hash('sha256', $bytes))) {
            throw ValidationException::withMessages([$field => 'SHA-256 does not match the decoded asset.']);
        }
    }

    private function verifyStoredRelease(BrassShowroomFrontendRelease $release): void
    {
        $disk = Storage::disk('local');
        foreach ([[$release->javascript_path, $release->javascript_sha256], [$release->stylesheet_path, $release->stylesheet_sha256]] as [$path, $hash]) {
            if (! $disk->exists($path) || ! hash_equals($hash, hash('sha256', $disk->get($path)))) {
                throw ValidationException::withMessages(['release_id' => 'Stored release files failed their integrity check.']);
            }
        }
    }

    private function modelMetadata(BrassShowroomFrontendRelease $release): array
    {
        return [
            'id' => $release->id, 'version' => $release->version, 'note' => $release->note,
            'javascript_sha256' => $release->javascript_sha256, 'stylesheet_sha256' => $release->stylesheet_sha256,
            'javascript_bytes' => $release->javascript_bytes, 'stylesheet_bytes' => $release->stylesheet_bytes,
            'created_by' => $release->created_by, 'activated_by' => $release->activated_by,
            'created_at' => $release->created_at?->toIso8601String(), 'activated_at' => $release->activated_at?->toIso8601String(),
        ];
    }
}
