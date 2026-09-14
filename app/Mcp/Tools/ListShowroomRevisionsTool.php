<?php

namespace App\Mcp\Tools;

use App\Services\BrassShowroomManager;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
final class ListShowroomRevisionsTool extends ShowroomTool
{
    public function name(): string
    {
        return 'list_showroom_revisions';
    }

    public function description(): string
    {
        return 'List the latest 20 published showroom revisions and audit notes so a previous version can be selected for restoration.';
    }

    public function handle(Request $request, BrassShowroomManager $showroom): Response
    {
        $this->userId($request);

        return $this->json(['revisions' => $showroom->revisions()]);
    }
}
