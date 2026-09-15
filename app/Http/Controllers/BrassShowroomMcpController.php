<?php

namespace App\Http\Controllers;

use App\Mcp\BrassShowroomToolRegistry;
use App\Support\BrassShowroomAuthorization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class BrassShowroomMcpController extends Controller
{
    private const PROTOCOL_VERSION = '2025-06-18';

    public function __invoke(Request $request, BrassShowroomToolRegistry $tools): Response
    {
        if ((int) $request->server('CONTENT_LENGTH', 0) > (int) config('brass-showroom.max_mcp_request_bytes', 12 * 1024 * 1024)) {
            return $this->rpcError(null, -32600, 'Request body is too large.', 413);
        }

        $message = $request->json()->all();
        if (! is_array($message) || array_is_list($message) || ($message['jsonrpc'] ?? null) !== '2.0' || ! is_string($message['method'] ?? null)) {
            return $this->rpcError($message['id'] ?? null, -32600, 'Invalid JSON-RPC request.');
        }

        $id = $message['id'] ?? null;
        $method = $message['method'];
        $params = is_array($message['params'] ?? null) ? $message['params'] : [];

        if ($method === 'notifications/initialized' || str_starts_with($method, 'notifications/')) {
            return response('', 202);
        }

        try {
            if (! BrassShowroomAuthorization::allows($request->user())) {
                throw new AuthorizationException('You are not allowed to manage the brass showroom.');
            }

            $result = match ($method) {
                'initialize' => [
                    'protocolVersion' => self::PROTOCOL_VERSION,
                    'capabilities' => ['tools' => ['listChanged' => false]],
                    'serverInfo' => ['name' => 'Unyilay Brass Showroom Manager', 'version' => '2.0.0'],
                    'instructions' => 'Read draft, edit, create a signed preview, and publish only after review. For JS/CSS, read status, stage verified bundles, preview, then activate a reviewed release.',
                ],
                'ping' => (object) [],
                'tools/list' => ['tools' => $tools->definitions($request->user())],
                'tools/call' => $this->callTool($tools, $request, $params),
                default => throw new \BadMethodCallException('Method not found.'),
            };

            return $this->rpcResult($id, $result);
        } catch (ValidationException $error) {
            return $this->rpcResult($id, $this->toolError('Validation failed.', $error->errors()));
        } catch (AuthorizationException $error) {
            return $this->rpcError($id, -32001, $error->getMessage(), 403);
        } catch (\BadMethodCallException $error) {
            return $this->rpcError($id, -32601, 'Method not found.');
        } catch (Throwable $error) {
            report($error);
            return $this->rpcResult($id, $this->toolError('The tool could not complete the request.'));
        }
    }

    private function callTool(BrassShowroomToolRegistry $tools, Request $request, array $params): array
    {
        validator($params, [
            'name' => ['required', 'string', 'max:80'],
            'arguments' => ['nullable', 'array'],
        ])->validate();

        return $tools->call($params['name'], $params['arguments'] ?? [], $request->user());
    }

    private function toolError(string $message, array $details = []): array
    {
        return [
            'content' => [['type' => 'text', 'text' => json_encode(['error' => $message, 'details' => $details], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]],
            'isError' => true,
        ];
    }

    private function rpcResult(string|int|null $id, mixed $result): JsonResponse
    {
        return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result])
            ->header('MCP-Protocol-Version', self::PROTOCOL_VERSION);
    }

    private function rpcError(string|int|null $id, int $code, string $message, int $status = 200): JsonResponse
    {
        return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]], $status)
            ->header('MCP-Protocol-Version', self::PROTOCOL_VERSION);
    }
}
