<?php

namespace App\Mcp\Tools;

use App\Services\BrassShowroomManager;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('update_showroom_layout_draft')]
#[Description('Update the private showroom layout draft: room colors, ambient and bounce light, shared spotlight color, exposure, and bench visibility or position. Omitted fields stay unchanged. Artwork placement is changed with update_artwork_draft.')]
#[IsDestructive(false)]
#[IsIdempotent]
final class UpdateShowroomLayoutTool extends ShowroomTool
{
    public function handle(Request $request, BrassShowroomManager $showroom): Response
    {
        $input = $request->validate([
            'wall_color' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'floor_color' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'ceiling_color' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'spotlight_color' => ['sometimes', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'ambient_intensity' => ['sometimes', 'numeric', 'between:0,3'],
            'hemisphere_intensity' => ['sometimes', 'numeric', 'between:0,4'],
            'bounce_intensity' => ['sometimes', 'numeric', 'between:0,12'],
            'tone_mapping_exposure' => ['sometimes', 'numeric', 'between:0.5,2'],
            'bench_visible' => ['sometimes', 'boolean'],
            'bench_x' => ['sometimes', 'numeric', 'between:-1,1'],
            'bench_z' => ['sometimes', 'numeric', 'between:-1,1'],
        ]);

        return $this->json($showroom->updateLayout($input, $this->userId($request)));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'wall_color' => $schema->string()->description('Six-digit hex color such as #e7dfcd.'),
            'floor_color' => $schema->string()->description('Six-digit hex color.'),
            'ceiling_color' => $schema->string()->description('Six-digit hex color.'),
            'spotlight_color' => $schema->string()->description('Six-digit hex color for artwork downlights.'),
            'ambient_intensity' => $schema->number(), 'hemisphere_intensity' => $schema->number(),
            'bounce_intensity' => $schema->number(), 'tone_mapping_exposure' => $schema->number(),
            'bench_visible' => $schema->boolean(), 'bench_x' => $schema->number(), 'bench_z' => $schema->number(),
        ];
    }
}
