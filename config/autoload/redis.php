<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'host' => env('REDIS_HOST'),
        'auth' => env('REDIS_AUTH'),
        'port' => (int) env('REDIS_PORT'),
        'db' => (int) env('REDIS_DB'),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME'),
        ],
    ],
    // 数据缓存
    'cache' => [
        'host' => env('CACHE_REDIS_HOST'),
        'auth' => env('CACHE_REDIS_AUTH'),
        'port' => (int) env('CACHE_REDIS_PORT'),
        'db' => (int) env('CACHE_REDIS_DB'),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 20,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('CACHE_REDIS_MAX_IDLE_TIME'),
        ],
    ],
    // 模型缓存用redis配置
    'model_cache' => [
        'host' => env('MODEL_CACHE_REDIS_HOST'),
        'auth' => env('MODEL_CACHE_REDIS_AUTH'),
        'port' => (int) env('MODEL_CACHE_REDIS_PORT'),
        'db' => (int) env('MODEL_CACHE_REDIS_DB'),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 20,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('MODEL_CACHE_REDIS_MAX_IDLE_TIME'),
        ],
    ],
];
