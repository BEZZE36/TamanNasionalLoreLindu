<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | Your Midtrans server key. This is used for server-side API calls.
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | Your Midtrans client key. This is used for client-side (Snap) integration.
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Merchant ID
    |--------------------------------------------------------------------------
    |
    | Your Midtrans merchant ID.
    |
    */
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Production Mode
    |--------------------------------------------------------------------------
    |
    | Set to true for production environment, false for sandbox.
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Sanitize Request
    |--------------------------------------------------------------------------
    |
    | Set to true to sanitize the request parameters.
    |
    */
    'is_sanitized' => true,

    /*
    |--------------------------------------------------------------------------
    | 3DS Transaction
    |--------------------------------------------------------------------------
    |
    | Set to true to enable 3D Secure for credit card transactions.
    |
    */
    'is_3ds' => true,

    /*
    |--------------------------------------------------------------------------
    | Snap API URLs
    |--------------------------------------------------------------------------
    */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false) 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    /*
    |--------------------------------------------------------------------------
    | Enabled Payment Methods
    |--------------------------------------------------------------------------
    |
    | List of enabled payment methods for Midtrans Snap.
    |
    */
    'enabled_payments' => [
        // Bank Transfer
        'bca_va',
        'bni_va', 
        'bri_va',
        'permata_va',
        'other_va',
        
        // E-Wallet
        'gopay',
        'shopeepay',
        'qris',
        
        // Convenience Store
        'alfamart',
        'indomaret',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Expiry
    |--------------------------------------------------------------------------
    |
    | Default expiry time for transactions in minutes.
    |
    */
    'expiry_duration' => 60 * 24, // 24 hours

    /*
    |--------------------------------------------------------------------------
    | Callback URL
    |--------------------------------------------------------------------------
    */
    'notification_url' => env('APP_URL') . '/api/payment/notification',
    'finish_url' => env('APP_URL') . '/booking/finish',
    'unfinish_url' => env('APP_URL') . '/booking/unfinish',
    'error_url' => env('APP_URL') . '/booking/error',
];
