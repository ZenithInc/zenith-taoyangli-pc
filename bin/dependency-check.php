#!/usr/bin/env php
<?php

declare(strict_types=1);

ini_set('display_errors', 'off');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

try {
    require BASE_PATH . '/vendor/autoload.php';
    Hyperf\Di\ClassLoader::init();
    $container = require BASE_PATH . '/config/container.php';
    $container->get(Hyperf\Contract\ApplicationInterface::class);
    $result = $container->get(App\Health\DependencyChecker::class)->run();
    fwrite(STDOUT, json_encode($result, JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(($result['status'] ?? 'failed') === 'ok' ? 0 : 1);
} catch (Throwable $throwable) {
    fwrite(STDOUT, json_encode([
        'status' => 'failed',
        'error_type' => $throwable::class,
    ], JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(1);
}
