<?php

return [
    'site_url' => env('WC_SITE_URL', 'https://unyilaysilver.com'),
    'consumer_key' => env('WC_CONSUMER_KEY'),
    'consumer_secret' => env('WC_CONSUMER_SECRET'),
    'uploads_url' => env('WP_UPLOADS_URL', rtrim(env('WC_SITE_URL', 'https://unyilaysilver.com'), '/') . '/wp-content/uploads'),
];
