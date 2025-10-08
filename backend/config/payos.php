<?php

return [
    'client_id' => env('PAYOS_CLIENT_ID'),
    'api_key' => env('PAYOS_API_KEY'),
    'checksum_key' => env('PAYOS_CHECKSUM_KEY'),
    'return_url' => env('PAYOS_RETURN_URL', 'http://localhost:3000/'),
    'cancel_url' => env('PAYOS_CANCEL_URL', 'http://localhost:3000/'),
    'webhook_url' => env('PAYOS_WEBHOOK_URL', 'http://localhost:8000/api/payment/callback'),
];
