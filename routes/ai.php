<?php

use App\Mcp\Servers\BrassShowroomServer;
use Laravel\Mcp\Facades\Mcp;

// Remote Codex app connection. HTTPS is required in production.
Mcp::web('/mcp/brass-showroom', BrassShowroomServer::class)
    ->middleware(['auth:sanctum', 'throttle:mcp-showroom']);
