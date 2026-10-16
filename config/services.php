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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Supplier Feeds
    |--------------------------------------------------------------------------
    |
    | Optional supplier feeds that are registered automatically in the admin
    | panel. Each entry is keyed by slug. Credentials should come from .env.
    |
    | 'example-supplier' => [
    |     'name' => 'Example Supplier',
    |     'storefront_code' => '10',
    |     'price_url' => env('EXAMPLE_SUPPLIER_FEED_URL'),
    |     'product_data_url' => env('EXAMPLE_SUPPLIER_PRODUCT_DATA_URL'),
    |     'feed_type' => 'xml', // csv, xml or json
    |     'auth_type' => null,  // null, basic or query
    |     'username' => env('EXAMPLE_SUPPLIER_USERNAME'),
    |     'password' => env('EXAMPLE_SUPPLIER_PASSWORD'),
    | ],
    |
    */

    'suppliers' => [],

];
