<?php

return [

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

    'admin' => [
        'payment_email' => env('ADMIN_PAYMENT_EMAIL', env('ADMIN_ORDER_EMAIL')),
    ],

    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com/v1'),
        'origin_postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE', '00000'),
        'couriers' => env('BITESHIP_COURIERS', 'jne'),

        'store_name' => env('STORE_NAME', 'Nama toko belum dikonfigurasi'),
        'store_address' => env('STORE_ADDRESS', 'Alamat toko belum dikonfigurasi'),
        'bank_name' => env('STORE_BANK_NAME', 'Nama bank belum dikonfigurasi'),
        'bank_account_number' => env('STORE_BANK_ACCOUNT_NUMBER', 'Nomor rekening belum dikonfigurasi'),
        'bank_account_name' => env('STORE_BANK_ACCOUNT_NAME', 'Nama pemilik rekening belum dikonfigurasi'),
    ],

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'client_email' => env('FIREBASE_CLIENT_EMAIL'),
        'private_key' => env('FIREBASE_PRIVATE_KEY'),
        'web' => [
            'api_key' => env('FIREBASE_WEB_API_KEY'),
            'auth_domain' => env('FIREBASE_WEB_AUTH_DOMAIN'),
            'project_id' => env('FIREBASE_WEB_PROJECT_ID', env('FIREBASE_PROJECT_ID')),
            'storage_bucket' => env('FIREBASE_WEB_STORAGE_BUCKET'),
            'messaging_sender_id' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
            'app_id' => env('FIREBASE_WEB_APP_ID'),
            'measurement_id' => env('FIREBASE_WEB_MEASUREMENT_ID'),
            'vapid_key' => env('FIREBASE_WEB_VAPID_KEY'),
            'default_title' => env('FIREBASE_WEB_DEFAULT_TITLE', 'Admin Purplestuff'),
        ],
    ],

];
