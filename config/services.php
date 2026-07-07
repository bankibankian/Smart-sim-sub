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

    'palmpay' => [
        'base_url'     => env('BASE_URL_PALMPAY', 'https://open-gw-prod.palmpay-inc.com/'),
        'bearer_token' => env('BEARER_TOKEN'),
        'merchant_id'  => env('MERCHANTID'),
        'version'      => env('VERSION', 'V2.0'),
        'notify_url'   => env('NOTIFY_URL'),
    ],

    'vtpass' => [
        'api_key'         => env('VTPASS_API_KEY'),
        'public_key'      => env('VTPASS_PUBLIC_KEY'),
        'secret_key'      => env('VTPASS_SECRET_KEY'),
        'payment_url'     => env('VTPASS_PAYMENT_URL', 'https://sandbox.vtpass.com/api/pay'),
        'variation_url'   => env('VTPASS_VARIATION_URL', 'https://sandbox.vtpass.com/api/service-variations?serviceID='),
        'verify_jamb_url' => env('VTPASS_VERIFY_JAMB_URL', 'https://sandbox.vtpass.com/api/verify-jamb'),
        'biller_code'     => env('VTPASS_BILLER_CODE'),
    ],
];
