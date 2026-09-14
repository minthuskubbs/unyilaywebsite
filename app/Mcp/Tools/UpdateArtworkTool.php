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

#[Name('update_artwork_draft')]
#[Description('Update one artwork in the private draft. Supports Myanmar/English titles, MMK price, framed dimensions, wall and slot, hanging height, display scale, downlight intensity, and crop rectangle. Omitted fields stay unchanged. This does not publish.')]
#[IsDestructive(false)]
#[IsIdempotent]
final class UpdateArtworkTool extends ShowroomTool
{
    public function handle(Request $request, BrassShowroomManager $showroom): Response
    {
        $input = $request->validate([
            'id' => ['required', 'alpha_dash:ascii', 'max:40'],
            'title_my' => ['sometimes', 'string', 'max:120'],
            'title_en' => ['sometimes', 'string', 'max:120'],
            'price_mmk' => ['sometimes', 'integer', 'between:0,1000000000'],
            'width_inches' => ['sometimes', 'numeric', 'between:4,80'],
            'height_inches' => ['sometimes', 'numeric', 'between:4,80'],
            'orientation' => ['sometimes', 'in:landscape,portrait'],
            'wall' => ['sometimes', 'in:left,right,back'],
            'slot' => ['sometimes', 'numeric', 'between:-1.5,1.5'],
            'height' => ['sometimes', 'numeric', 'between:0.8,2.2'],
            'scale' => ['sometimes', 'numeric', 'between:0.5,1.6'],
            'light_intensity' => ['sometimes', 'numeric', 'between:0,15'],
            'crop_x' => ['sometimes', 'integer', 'min:0', 'max:20000', 'required_with:crop_y,crop_width,crop_height'],
            'crop_y' => ['sometimes', 'integer', 'min:0', 'max:20000', 'required_with:crop_x,crop_width,crop_height'],
            'crop_width' => ['sometimes', 'integer', 'between:1,20000', 'required_with:crop_x,crop_y,crop_height'],
            'crop_height' => ['sometimes', 'integer', 'between:1,20000', 'required_with:crop_x,crop_y,crop_width'],
        ]);
        $id = $input['id'];
        unset($input['id']);

        return $this->json($showroom->updateArtwork($id, $input, $this->userId($request)));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()->description('Stable artwork id from get_showroom_state.')->required(),
            'title_my' => $schema->string()->description('Myanmar title.'),
            'title_en' => $schema->string()->description('English title.'),
            'price_mmk' => $schema->integer()->description('Whole kyat price.'),
            'width_inches' => $schema->number()->description('Outer frame width in inches.'),
            'height_inches' => $schema->number()->description('Outer frame height in inches.'),
            'orientation' => $schema->string()->enum(['landscape', 'portrait']),
            'wall' => $schema->string()->enum(['left', 'right', 'back']),
            'slot' => $schema->number()->description('Horizontal position on back wall or depth position on side wall, -1.5 to 1.5.'),
            'height' => $schema->number()->description('Artwork center height in scene meters, 0.8 to 2.2.'),
            'scale' => $schema->number()->description('Visual scale multiplier, 0.5 to 1.6.'),
            'light_intensity' => $schema->number()->description('Artwork downlight intensity, 0 to 15.'),
            'crop_x' => $schema->integer(), 'crop_y' => $schema->integer(),
            'crop_width' => $schema->integer(), 'crop_height' => $schema->integer(),
        ];
    }
}
