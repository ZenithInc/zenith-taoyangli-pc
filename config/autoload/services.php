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
    //    'consumers' => [
    //        [
    //            // The service name, this name should as same as with the name of service provider.
    //            'name' => 'YourServiceName',
    //            // The service registry, if `nodes` is missing below, then you should provide this configs.
    //            'registry' => [
    //                'protocol' => 'consul',
    //                'address' => 'Enter the address of service registry',
    //            ],
    //            // If `registry` is missing, then you should provide the nodes configs.
    //            'nodes' => [
    //                // Provide the host and port of the service provider.
    //                // ['host' => 'The host of the service provider', 'port' => 9502]
    //            ],
    //        ],
    //    ],
    'consumers' => value(function () {
        $consumers = [];
        // 这里示例自动创建代理消费者类的配置形式，顾存在 name 和 service 两个配置项，这里的做法不是唯一的，仅说明可以通过 PHP 代码来生成配置
        $services = [
            'ParkApiRpcService' => App\JsonRpc\ParkApiRpcService::class,
        ];
        foreach ($services as $name => $interface) {
            $consumers[] = [
                'name'    => $name,
                'service' => $interface,
                'nodes'   => [// alpha 47.111.13.130 9531
                              ['host' => env('rpc_node_host', 'host.docker.internal'), 'port' => (int) env('rpc_node_port', '9525')],
                ],
                'options' => [
                    'connect_timeout' => 5.0,
                    'recv_timeout'    => 5.0,
                    'settings'        => [
                        // 根据协议不同，区分配置
                        'open_eof_split'     => true,
                        'package_eof'        => "\r\n",
                        'package_max_length' => 1024 * 1024 * 2,
                        // 'open_length_check' => true,
                        // 'package_length_type' => 'N',
                        // 'package_length_offset' => 0,
                        // 'package_body_offset' => 4,
                    ],
                    // 重试次数，默认值为 2，收包超时不进行重试。暂只支持 JsonRpcPoolTransporter
                    'retry_count'     => 2,
                    // 重试间隔，毫秒
                    'retry_interval'  => 100,
                    // 使用多路复用 RPC 时的心跳间隔，null 为不触发心跳
                    'heartbeat'       => 30,
                    // 当使用 JsonRpcPoolTransporter 时会用到以下配置
                    'pool'            => [
                        'min_connections' => 1,
                        'max_connections' => 32,
                        'connect_timeout' => 10.0,
                        'wait_timeout'    => 3.0,
                        'heartbeat'       => -1,
                        'max_idle_time'   => 60.0,
                    ],
                ],
            ];
        }

        return $consumers;
    }),
];
