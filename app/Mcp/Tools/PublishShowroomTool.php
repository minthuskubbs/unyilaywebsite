<?php

namespace App\Mcp\Tools;

use App\Services\BrassShowroomManager;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsDestructive]
#[IsIdempotent]
final class PublishShowroomTool extends ShowroomTool
{
    public function name(): string
    {
        return 'publish_showroom';
    }

    public function description(): string
    {
        return 'Publish the reviewed draft atomically to the public showroom. Call get_showroom_state and create_showroom_preview first. The expected draft revision prevents publishing stale or concurrently changed data.';
    }

    public function handle(Request $request, BrassShowroomManager $showroom): Response
    {
        $input = $request->validate([
            'expected_draft_revision' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:240'],
        ]);

        return $this->json($showroom->publish($input['expected_draft_revision'], $input['note'] ?? null, $this->userId($request)));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'expected_draft_revision' => $schema->integer()->description('Exact draft_revision returned after review.')->required(),
            'note' => $schema->string()->description('Short audit note describing the publication.'),
        ];
    }
}
