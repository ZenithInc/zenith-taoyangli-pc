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

$logDir = rtrim(env('LOG_DIR', BASE_PATH . '/runtime'), '/') . '/logs';
$driver = env('LOG_DRIVER', 'stdout');
$handlers = [];
$formatter = [
    'class' => App\Logging\OpsJsonFormatter::class,
    'constructor' => [
        'batchMode' => Monolog\Formatter\JsonFormatter::BATCH_MODE_JSON,
        'appendNewline' => true,
    ],
];

if ($driver === 'stdout') {
    $handlers = [[
        'class' => Monolog\Handler\StreamHandler::class,
        'constructor' => [
            'stream' => 'php://stdout',
            'level' => Monolog\Logger::DEBUG,
        ],
        'formatter' => $formatter,
    ]];
}

if ($driver == 'file') {
    $handlers = [
        [
            'class' => App\Core\Common\Handler\LogFileHandler::class,
            'constructor' => [
                'stream' => $logDir.'/info.log',
                'level' => Monolog\Logger::INFO,
            ],
            'formatter' => $formatter
        ],

        [
            'class' => App\Core\Common\Handler\LogFileHandler::class,
            'constructor' => [
                'stream' => $logDir.'/debug.log',
                'level' => Monolog\Logger::DEBUG,
            ],
            'formatter' => $formatter
        ],

        [
            'class' => App\Core\Common\Handler\LogFileHandler::class,
            'constructor' => [
                'stream' => $logDir.'/error.log',
                'level' => Monolog\Logger::ERROR,
            ],
            'formatter' => $formatter
        ],
    ];
}

if ($driver == 'aliyun') {
    $handlers = [
        [
            'class' => App\Core\Common\Handler\LogAliyunHandler::class,
            'formatter' => $formatter
        ],
    ];
}


return [
    'default' => [
        'handlers' => $handlers
    ],
];
