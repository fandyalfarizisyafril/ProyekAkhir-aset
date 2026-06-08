<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR Public Base URL
    |--------------------------------------------------------------------------
    |
    | Pakai URL ini untuk payload QR ketika aplikasi berjalan di jaringan lokal
    | atau domain publik. Contoh: http://192.168.1.20:8000 atau https://aset.go.id.
    |
    */
    'public_base_url' => env('QR_PUBLIC_BASE_URL', env('APP_URL', 'http://localhost')),
];
