<?php

declare(strict_types=1);

return [
    'checks' => [
        'mysql-default-read' => ['type' => 'mysql', 'host_key' => 'DB_MASTER_HOST', 'prefix' => 'DB_'],
        'mysql-default-write' => ['type' => 'mysql', 'host_key' => 'DB_SLAVE_HOST', 'prefix' => 'DB_'],
        'redis-default' => ['type' => 'redis', 'prefix' => 'REDIS_'],
        'redis-cache' => ['type' => 'redis', 'prefix' => 'CACHE_REDIS_'],
        'redis-model-cache' => ['type' => 'redis', 'prefix' => 'MODEL_CACHE_REDIS_'],
        'orders-rpc' => ['type' => 'tcp', 'host_key' => 'rpc_node_host', 'port_key' => 'rpc_node_port'],
        'object-storage-http' => ['type' => 'url', 'url_key' => 'UPYUN_IMAGE_URL'],
    ],
];
