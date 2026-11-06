<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    // کنسول ملی پیامک — کلید API از console.melipayamak.com
    'melipayamak' => [
        'key' => env('MELIPAYAMAK_API_KEY'),
        'from' => env('MELIPAYAMAK_FROM'),
        'otp_body_id' => (int) env('MELIPAYAMAK_OTP_BODY_ID', 0),
        // اگر خالی باشد از همان الگوی بازیابی رمز استفاده می‌شود
        'register_body_id' => (int) env('MELIPAYAMAK_REGISTER_BODY_ID', env('MELIPAYAMAK_OTP_BODY_ID', 0)),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
