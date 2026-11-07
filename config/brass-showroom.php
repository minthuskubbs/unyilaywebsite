<?php

return [
    'key' => env('BRASS_SHOWROOM_KEY', 'main'),
    'manager_user_ids' => array_values(array_filter(array_map('intval', explode(',', env('BRASS_SHOWROOM_MANAGER_IDS', ''))))),
    'manager_emails' => array_values(array_filter(array_map('trim', explode(',', env('BRASS_SHOWROOM_MANAGER_EMAILS', ''))))),
    'max_image_bytes' => 8 * 1024 * 1024,
    'max_image_pixels' => 20_000_000,
    'max_artworks' => 250,
    'preview_minutes' => 20,
    'frontend_deploy_enabled' => env('BRASS_SHOWROOM_FRONTEND_DEPLOY_ENABLED', false),
    'max_frontend_javascript_bytes' => 2 * 1024 * 1024,
    'max_frontend_stylesheet_bytes' => 256 * 1024,
    'frontend_deploys_per_hour' => 6,
    'max_mcp_request_bytes' => 12 * 1024 * 1024,

    // Prices follow Notion: `php artisan brass-showroom:sync-notion` (scheduled every six hours)
    // copies "Sale Price" from these databases to the live showroom. Only prices change.
    'notion' => [
        'sync_enabled' => env('BRASS_SHOWROOM_NOTION_SYNC_ENABLED', false),
        'token' => env('BRASS_SHOWROOM_NOTION_TOKEN'),
        'databases' => array_values(array_filter(array_map('trim', explode(',', env('BRASS_SHOWROOM_NOTION_DATABASES',
            '27a7ca93-f410-8056-bed1-f09412d88253,27a7ca93-f410-80a9-9d3b-c2350f4fce35'))))),
        'price_property' => env('BRASS_SHOWROOM_NOTION_PRICE_PROPERTY', 'Sale Price'),
        'version' => '2022-06-28',
    ],
];
