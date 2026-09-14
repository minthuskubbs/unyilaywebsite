<?php

namespace App\Mcp\Tools;

use App\Services\BrassShowroomManager;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_showroom_state')]
#[Description('Read the complete draft or published brass showroom configuration, including artwork metadata, prices, images, placement, lighting, theme, and revision numbers. Read draft before any update and again before publishing.')]
#[IsReadOnly]
final class GetShowroomStateTool extends ShowroomTool
{
    public function handle(Request $request, BrassShowroomManager $showroom): Response
    {
        $this->userId($request);
        $input = $request->validate(['view' => ['nullable', 'in:draft,published']]);

        return $this->json($showroom->view($input['view'] ?? 'draft'));
    }

    public function schema(JsonSchema $schema): array
    {
        return ['view' => $schema->string()->enum(['draft', 'published'])->description('Configuration version to read. Defaults to draft.')];
    }
}
