<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateShowroomPreviewTool;
use App\Mcp\Tools\GetShowroomStateTool;
use App\Mcp\Tools\ListShowroomRevisionsTool;
use App\Mcp\Tools\PublishShowroomTool;
use App\Mcp\Tools\ReplaceArtworkImageTool;
use App\Mcp\Tools\RestoreShowroomRevisionTool;
use App\Mcp\Tools\UpdateArtworkTool;
use App\Mcp\Tools\UpdateShowroomLayoutTool;
use Laravel\Mcp\Server;

final class BrassShowroomServer extends Server
{
    protected string $name = 'Unyilay Brass Showroom Manager';

    protected string $version = '1.0.0';

    protected string $instructions = 'Always call get_showroom_state with view=draft before editing. Changes remain private drafts. After editing, call create_showroom_preview and ask the user to review the preview. Call publish_showroom only when the user explicitly asks to publish, using the exact latest draft_revision. Never invent artwork IDs or prices.';

    protected array $tools = [
        GetShowroomStateTool::class,
        UpdateArtworkTool::class,
        ReplaceArtworkImageTool::class,
        UpdateShowroomLayoutTool::class,
        CreateShowroomPreviewTool::class,
        PublishShowroomTool::class,
        ListShowroomRevisionsTool::class,
        RestoreShowroomRevisionTool::class,
    ];
}
