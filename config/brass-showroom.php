<?php

return [
    'key' => env('BRASS_SHOWROOM_KEY', 'main'),
    'manager_user_ids' => array_values(array_filter(array_map('intval', explode(',', env('BRASS_SHOWROOM_MANAGER_IDS', ''))))),
    'manager_emails' => array_values(array_filter(array_map('trim', explode(',', env('BRASS_SHOWROOM_MANAGER_EMAILS', ''))))),
    'max_image_bytes' => 8 * 1024 * 1024,
    'max_image_pixels' => 20_000_000,
    'preview_minutes' => 20,
];
