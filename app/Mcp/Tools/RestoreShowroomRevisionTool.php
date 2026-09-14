<?php

namespace App\Mcp\Tools;

use App\Services\BrassShowroomManager;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsDestructive(false)]
#[IsIdempotent]
final class RestoreShowroomRevisionTool extends ShowroomTool
{
    public function name(): string
    {
        return 'restore_showroom_revision_to_draft';
    }

    public function description(): string
    {
        return 'Copy a previous published revision into the private draft. It does not change the public showroom until publish_showroom is called after preview and review.';
    }

    public function handle(Request $request, BrassShowroomManager $showroom): Response
    {
        $input = $request->validate(['revision' => ['required', 'integer', 'min:1']]);

        return $this->json($showroom->restoreToDraft($input['revision'], $this->userId($request)));
    }

    public function schema(JsonSchema $schema): array
    {
        return ['revision' => $schema->integer()->description('Published revision number from list_showroom_revisions.')->required()];
    }
}
