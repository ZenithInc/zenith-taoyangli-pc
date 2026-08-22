<?php

declare(strict_types=1);

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__));
$swooleV6 = version_compare(SWOOLE_VERSION, '6.0.0', '>=');
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', $swooleV6 ? 0 : SWOOLE_HOOK_ALL);

if (getenv('DB_DRIVER') === false) {
    putenv('DB_DRIVER=mysql');
}
$enableCoroutine = new ReflectionMethod(Swoole\Runtime::class, 'enableCoroutine');
$parameters = $enableCoroutine->getParameters();
if ((string) $parameters[0]->getType() === 'int') {
    Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_FLAGS);
} else {
    Swoole\Runtime::enableCoroutine(true, SWOOLE_HOOK_FLAGS);
}
unset($enableCoroutine, $parameters);

require BASE_PATH . '/vendor/autoload.php';
Hyperf\Di\ClassLoader::init();
$container = require BASE_PATH . '/config/container.php';

if ($swooleV6) {
    $container->set(Hyperf\Utils\Waiter::class, new class extends Hyperf\Utils\Waiter {
        public function wait(Closure $closure, ?float $timeout = null)
        {
            if (Swoole\Coroutine::getCid() >= 0) {
                return $closure();
            }
            $result = null;
            $throwable = null;
            Swoole\Coroutine\run(static function () use ($closure, &$result, &$throwable): void {
                try {
                    $result = $closure();
                } catch (Throwable $exception) {
                    $throwable = $exception;
                }
            });
            if ($throwable !== null) {
                throw $throwable;
            }
            return $result;
        }
    });
}

$container->get(Hyperf\Contract\ApplicationInterface::class);
