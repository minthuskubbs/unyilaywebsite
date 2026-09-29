<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'woocommerce' => [
        'url'    => env('WC_SITE_URL', ''),
        'key'    => env('WC_CONSUMER_KEY', ''),
        'secret' => env('WC_CONSUMER_SECRET', ''),
        // Laravel and WordPress run on the same physical server, but the
        // public domain is proxied through Cloudflare, which challenges
        // server-to-server requests (wp-login.php, the WC REST API) as if
        // they were bots. Setting this to the server's own public IP makes
        // outbound calls connect directly to the origin (skipping the
        // Cloudflare round-trip) while still sending the real hostname for
        // TLS/virtual-host routing — see WordPressAuthService and
        // WooCommerceService. Leave blank to disable (normal DNS resolution).
        'origin_ip' => env('WC_ORIGIN_IP', ''),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

];
