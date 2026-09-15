<?php

namespace App\Http\Controllers;

use App\Services\BrassShowroomFrontendReleaseManager;
use App\Services\BrassShowroomManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

final class BrassShowroomController extends Controller
{
    public function show(BrassShowroomManager $showroom, BrassShowroomFrontendReleaseManager $frontend): View
    {
        return view('pages.brass', [
            'showroomConfig' => $showroom->view('published')['config'],
            'frontendAssetUrls' => $this->activeAssetUrls($frontend),
        ]);
    }

    public function preview(Request $request, BrassShowroomManager $showroom, BrassShowroomFrontendReleaseManager $frontend): View
    {
        abort_unless($request->hasValidSignature(), 403);

        return view('pages.brass', [
            'showroomConfig' => $showroom->view('draft')['config'],
            'frontendAssetUrls' => $this->activeAssetUrls($frontend),
        ]);
    }

    public function frontendPreview(Request $request, string $release, BrassShowroomManager $showroom, BrassShowroomFrontendReleaseManager $frontend): View
    {
        abort_unless($request->hasValidSignature(), 403);
        $frontend->metadata($release);
        $expires = now()->addMinutes((int) config('brass-showroom.preview_minutes', 20));

        return view('pages.brass', [
            'showroomConfig' => $showroom->view('draft')['config'],
            'frontendAssetUrls' => [
                'stylesheet' => URL::temporarySignedRoute('brass-showroom.frontend-preview-asset', $expires, ['release' => $release, 'asset' => 'stylesheet']),
                'javascript' => URL::temporarySignedRoute('brass-showroom.frontend-preview-asset', $expires, ['release' => $release, 'asset' => 'javascript']),
            ],
        ]);
    }

    public function frontendAsset(string $release, string $asset, BrassShowroomFrontendReleaseManager $frontend): Response
    {
        return $this->assetResponse($frontend->asset($release, $asset, true));
    }

    public function frontendPreviewAsset(Request $request, string $release, string $asset, BrassShowroomFrontendReleaseManager $frontend): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        return $this->assetResponse($frontend->asset($release, $asset));
    }

    private function activeAssetUrls(BrassShowroomFrontendReleaseManager $frontend): array
    {
        $release = $frontend->activeId();

        return [
            'stylesheet' => route('brass-showroom.frontend-asset', ['release' => $release, 'asset' => 'stylesheet']),
            'javascript' => route('brass-showroom.frontend-asset', ['release' => $release, 'asset' => 'javascript']),
        ];
    }

    private function assetResponse(array $asset): Response
    {
        $etag = '"'.$asset['sha256'].'"';
        if (request()->header('If-None-Match') === $etag) {
            return response('', 304, ['ETag' => $etag]);
        }

        return response($asset['bytes'], 200, [
            'Content-Type' => $asset['content_type'],
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'ETag' => $etag,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
