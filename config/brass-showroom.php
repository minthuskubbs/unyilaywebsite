<?php

return [
    'key' => env('BRASS_SHOWROOM_KEY', 'main'),
    'manager_user_ids' => array_values(array_filter(array_map('intval', explode(',', env('BRASS_SHOWROOM_MANAGER_IDS', ''))))),
    'manager_emails' => array_values(array_filter(array_map('trim', explode(',', env('BRASS_SHOWROOM_MANAGER_EMAILS', ''))))),
    'max_image_bytes' => 8 * 1024 * 1024,
    'max_image_pixels' => 20_000_000,
    'preview_minutes' => 20,
    'frontend_deploy_enabled' => env('BRASS_SHOWROOM_FRONTEND_DEPLOY_ENABLED', false),
    'max_frontend_javascript_bytes' => 2 * 1024 * 1024,
    'max_frontend_stylesheet_bytes' => 256 * 1024,
    'frontend_deploys_per_hour' => 6,
    'max_mcp_request_bytes' => 12 * 1024 * 1024,
];
