<?php

use App\Http\Controllers\BrassShowroomMcpController;
use Illuminate\Support\Facades\Route;

// Remote Codex app connection. HTTPS is required in production.
// Registered outside the "web" group (see RouteServiceProvider) so no
// session/CSRF middleware applies - auth is via Sanctum bearer token only.
Route::post('/mcp/brass-showroom', BrassShowroomMcpController::class)
    ->middleware(['auth:sanctum', 'throttle:mcp-showroom']);
