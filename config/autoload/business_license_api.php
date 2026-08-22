<?php

declare(strict_types=1);

return [
    'host' => env('BUSINESS_LICENSE_API_HOST'),
    'path' => env('BUSINESS_LICENSE_API_PATH'),
    'app_code' => env('BUSINESS_LICENSE_API_APP_CODE'),
    'timeout' => (int) env('BUSINESS_LICENSE_API_TIMEOUT'),
];
