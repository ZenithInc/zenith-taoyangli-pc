<?php
/**
 * Created by PhpStorm.
 * User: zhh
 * Date: 2021/9/1
 * Time: 10:34
 */
return [
    'idCard' => [
        'url'=>env('OCR_ID_CARD_URL'),
        'appcode'=>env('OCR_ID_CARD_APP_CODE')
    ],
    'idCardCheck' => [
        'appid' => env('OCR_ID_CARD_CHECK_APP_ID'),
        'appcode' => env('OCR_ID_CARD_CHECK_APP_CODE'),
        'url' => env('OCR_ID_CARD_CHECK_URL')
    ],
];
