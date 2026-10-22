<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inventory & PWA Configuration
    |--------------------------------------------------------------------------
    */

    'pwa_origin' => env('PWA_ORIGIN', 'http://localhost:5174'),

    'max_transaction_quantity' => (int) env('INVENTORY_MAX_TRANSACTION_QUANTITY', 500),

    'token_expiration_days' => (int) env('SANCTUM_TOKEN_EXPIRATION_DAYS', 30),

];
