<?php

namespace App\Mcp\Tools;

use App\Services\BrassShowroomManager;
use Illuminate\Support\Facades\URL;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
final class CreateShowroomPreviewTool extends ShowroomTool
{
    public function name(): string
    {
        return 'create_showroom_preview';
    }

    public function description(): string
    {
        return 'Create a short-lived signed preview URL for the current private draft. Use this after edits and before publishing. The URL expires after the configured number of minutes and does not expose the MCP token.';
    }

    public function handle(Request $request, BrassShowroomManager $showroom): Response
    {
        $this->userId($request);
        $state = $showroom->view('draft');
        $url = URL::temporarySignedRoute('brass-showroom.preview', now()->addMinutes(config('brass-showroom.preview_minutes', 20)));

        return $this->json(['preview_url' => $url, 'expires_in_minutes' => config('brass-showroom.preview_minutes', 20), 'draft_revision' => $state['draft_revision']]);
    }
}
