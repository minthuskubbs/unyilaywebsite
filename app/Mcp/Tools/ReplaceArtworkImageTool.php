<?php

namespace App\Mcp\Tools;

use App\Services\BrassShowroomManager;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive(false)]
final class ReplaceArtworkImageTool extends ShowroomTool
{
    public function name(): string
    {
        return 'replace_artwork_image_draft';
    }

    public function description(): string
    {
        return 'Upload a decoded PNG, JPEG, or WebP artwork image to private storage-backed draft configuration. Send base64 bytes or a data URL, maximum 8 MB by default. SVG and remote URLs are rejected. This does not publish.';
    }

    public function handle(Request $request, BrassShowroomManager $showroom): Response
    {
        $input = $request->validate([
            'id' => ['required', 'alpha_dash:ascii', 'max:40'],
            'filename' => ['required', 'string', 'max:120'],
            'image_base64' => ['required', 'string'],
        ]);

        return $this->json($showroom->replaceImage($input['id'], $input['image_base64'], $input['filename'], $this->userId($request)));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->description('Stable artwork id from get_showroom_state.')->required(),
            'filename' => $schema->string()->description('Original filename used only to create a safe storage name.')->required(),
            'image_base64' => $schema->string()->description('Base64 image bytes or data:image/...;base64 data URL.')->required(),
        ];
    }
}
