<?php

namespace App\Mcp\Tools;

use App\Support\BrassShowroomAuthorization;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

abstract class ShowroomTool extends Tool
{
    public function shouldRegister(Request $request): bool
    {
        return BrassShowroomAuthorization::allows($request->user());
    }

    protected function userId(Request $request): int
    {
        $user = $request->user();
        if (! BrassShowroomAuthorization::allows($user)) {
            throw new AuthorizationException('You are not allowed to manage the brass showroom.');
        }

        return (int) $user->getAuthIdentifier();
    }

    protected function json(array $payload): Response
    {
        return Response::text(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
