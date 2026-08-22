<?php

declare(strict_types=1);

namespace App\Core\Common\Handler;

use App\Core\Common\Facade\Log;
use App\Support\LogSanitizer;
use Hyperf\Context\Context;

class WriteLog
{
    public static function debug($log, $requestId = ''): void
    {
        self::write('debug', $log, $requestId);
    }

    public static function info($log, $requestId = ''): void
    {
        self::write('info', $log, $requestId);
    }

    public static function error($log, $requestId = ''): void
    {
        self::write('error', $log, $requestId);
    }

    private static function write(string $level, $log, $requestId): void
    {
        if (! config('log_enable')) {
            return;
        }

        $requestId = (string) ($requestId ?: Context::get('requestId') ?: Context::get('qid') ?: '');
        $message = is_string($log)
            ? LogSanitizer::sanitizeString($log)
            : json_encode(LogSanitizer::sanitize($log), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Log::get('')->{$level}((string) $message, ['request_id' => $requestId]);
    }
}
