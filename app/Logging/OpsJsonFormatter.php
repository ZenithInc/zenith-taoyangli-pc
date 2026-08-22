<?php

declare(strict_types=1);

namespace App\Logging;

use App\Support\LogSanitizer;
use Hyperf\Context\Context;
use Monolog\Formatter\JsonFormatter;

final class OpsJsonFormatter extends JsonFormatter
{
    public function format(array $record): string
    {
        $requestId = (string) (Context::get('requestId') ?: Context::get('qid') ?: '');
        $normalized = [
            'timestamp' => isset($record['datetime']) && method_exists($record['datetime'], 'format')
                ? $record['datetime']->format(DATE_ATOM)
                : date(DATE_ATOM),
            'service' => (string) (getenv('APP_NAME') ?: basename(BASE_PATH)),
            'role' => (string) (getenv('APP_ROLE') ?: 'http'),
            'level_name' => (string) ($record['level_name'] ?? 'INFO'),
            'channel' => (string) ($record['channel'] ?? 'app'),
            'request_id' => $requestId,
            'requestId' => $requestId,
            'qid' => $requestId,
            'message' => LogSanitizer::sanitizeString((string) ($record['message'] ?? '')),
            'context' => LogSanitizer::sanitize($record['context'] ?? []),
            'extra' => LogSanitizer::sanitize($record['extra'] ?? []),
        ];

        return parent::format($normalized);
    }
}
