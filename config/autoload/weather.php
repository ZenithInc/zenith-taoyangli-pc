<?php
/**
 * Created by PhpStorm.
 * User: zhh
 * Date: 2021/7/20
 * Time: 10:36
 */
return [
    'moji'=>[
        'base_url'=>rtrim((string) env('WEATHER_MOJI_API_BASE_URL'), '/'),
        'appkey'=>env('WEATHER_MOJI_APP_KEY'),
        'appsecret'=>env('WEATHER_MOJI_APP_SECRET'),
        'appcode'=>env('WEATHER_MOJI_APP_CODE')
    ]
];
