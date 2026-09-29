<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;

/**
 * Pins an outbound HTTP client to connect directly to the WordPress
 * origin server's IP instead of resolving the public domain through
 * Cloudflare — see the comment on config('services.woocommerce.origin_ip').
 */
class OriginPinnedHttp
{
    public static function apply(PendingRequest $request, string $url): PendingRequest
    {
        $ip = config('services.woocommerce.origin_ip');
        if (!$ip) {
            return $request;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return $request;
        }

        return $request->withOptions([
            'curl' => [
                CURLOPT_RESOLVE => ["{$host}:443:{$ip}", "{$host}:80:{$ip}"],
            ],
        ]);
    }
}
