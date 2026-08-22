<?php

declare(strict_types=1);

namespace App\Support;

final class LogSanitizer
{
    private const OMITTED_KEYS = [
        'body',
        'content',
        'contents',
        'cookie',
        'headers',
        'payload',
        'raw',
        'request',
        'request_body',
        'response',
        'response_body',
    ];

    private const SECRET_KEY_PATTERN = '/(?:authorization|credential|password|passwd|secret|signature|sign_key|token|aes_key|aes_iv|private_key)/i';

    private const IDENTITY_KEY_PATTERN = '/(?:cert_no|id_card|idcard|identity|mobile|openid|phone|tel|unionid|user_id|userid|wx_openid)/i';

    private const PAYMENT_KEY_PATTERN = '/(?:payment_id|payment_no|refund_id|refund_no|transaction_id)/i';

    public static function sanitize($value, ?string $key = null)
    {
        if ($key !== null) {
            $normalizedKey = strtolower($key);
            if (is_string($value) && preg_match('/(?:url|uri|endpoint)$/i', $normalizedKey) === 1) {
                return self::sanitizeUrl($value);
            }
            if (in_array($normalizedKey, self::OMITTED_KEYS, true)) {
                return '[omitted]';
            }
            if (preg_match(self::SECRET_KEY_PATTERN, $normalizedKey) === 1) {
                return '[redacted]';
            }
            if (preg_match(self::PAYMENT_KEY_PATTERN, $normalizedKey) === 1) {
                return self::paymentReference($value);
            }
            if (preg_match(self::IDENTITY_KEY_PATTERN, $normalizedKey) === 1) {
                return self::digest($value);
            }
        }

        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $itemKey => $itemValue) {
                $sanitized[$itemKey] = self::sanitize($itemValue, is_string($itemKey) ? $itemKey : null);
            }
            return $sanitized;
        }

        if (is_object($value)) {
            return '[object:' . get_class($value) . ']';
        }

        if (is_resource($value)) {
            return '[resource]';
        }

        if (is_string($value)) {
            return self::sanitizeString($value);
        }

        return $value;
    }

    public static function sanitizeString(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', $value);
        $value = self::summarizeJsonFragments($value);
        $value = preg_replace_callback(
            '/\bhttps?:\/\/[^\s|<>"\']+/i',
            static fn (array $match): string => self::stripUrlQuery($match[0]),
            $value
        ) ?? $value;
        $value = preg_replace_callback(
            '/((?:param|params|payload|body|form_data|request_data|入参|传数|请求参数)\s*[:：|]\s*)(.+)$/iu',
            static function (array $match): string {
                if (str_contains($match[2], '[json-summary:')) {
                    return $match[1] . $match[2];
                }

                return $match[1] . self::opaqueSummary('body', $match[2]);
            },
            $value
        ) ?? $value;

        $value = preg_replace_callback(
            '/(?<!\d)1[3-9]\d{9}(?!\d)/',
            static fn (array $match): string => '[phone:' . substr(hash('sha256', $match[0]), 0, 16) . ']',
            $value
        ) ?? $value;

        $value = preg_replace_callback(
            '/(?<![0-9A-Za-z])\d{17}[0-9Xx](?![0-9A-Za-z])/',
            static fn (array $match): string => '[identity:' . substr(hash('sha256', strtoupper($match[0])), 0, 16) . ']',
            $value
        ) ?? $value;

        $value = preg_replace('/\b(?:Bearer|Basic)\s+[A-Za-z0-9._~+\/-]+=*/i', '[authorization:redacted]', $value) ?? $value;
        $value = preg_replace('/\b(password|passwd|secret|signature|sign|token|authorization)=([^\s&]+)/i', '$1=[redacted]', $value) ?? $value;

        return $value;
    }

    public static function sanitizeUrl(string $value): string
    {
        return self::sanitizeString(self::stripUrlQuery($value));
    }

    private static function stripUrlQuery(string $value): string
    {
        $value = str_replace(["\r", "\n"], '', $value);
        $cutAt = strlen($value);
        foreach (['?', '#'] as $separator) {
            $position = strpos($value, $separator);
            if ($position !== false) {
                $cutAt = min($cutAt, $position);
            }
        }

        return substr($value, 0, $cutAt);
    }


    public static function externalLogRecord($method, $url, $request, $requestTime, $response): array
    {
        $requestBody = is_string($request) ? $request : json_encode($request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $responseBody = is_string($response) ? $response : json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $responseData = is_array($response) ? $response : json_decode((string) $responseBody, true);
        $responseSummary = [
            'type' => is_object($response) ? get_class($response) : gettype($response),
            'bytes' => strlen((string) $responseBody),
        ];
        if (is_array($responseData)) {
            foreach (['return_code', 'errcode', 'code', 'status'] as $key) {
                if (isset($responseData[$key]) && is_scalar($responseData[$key])) {
                    $responseSummary[$key] = $responseData[$key];
                }
            }
        }

        return [
            'method' => self::sanitizeString((string) $method),
            'url' => self::sanitizeUrl((string) $url),
            'require' => json_encode([
                'bytes' => strlen((string) $requestBody),
                'sha256' => hash('sha256', (string) $requestBody),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'require_time' => $requestTime,
            'response' => json_encode(self::sanitize($responseSummary), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    private static function digest($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $encoded = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_SLASHES);
        return 'sha256:' . substr(hash('sha256', (string) $encoded), 0, 16);
    }

    private static function paymentReference($value): string
    {
        if (! is_scalar($value) || $value === '') {
            return self::digest($value);
        }

        $reference = (string) $value;
        return '…' . substr($reference, -6);
    }

    private static function summarizeJsonFragments(string $value): string
    {
        $length = strlen($value);
        $sanitized = '';

        for ($offset = 0; $offset < $length;) {
            if ($value[$offset] !== '{' && $value[$offset] !== '[') {
                $sanitized .= $value[$offset++];
                continue;
            }

            $end = self::jsonFragmentEnd($value, $offset);
            if ($end === null) {
                $sanitized .= $value[$offset++];
                continue;
            }

            $fragment = substr($value, $offset, $end - $offset + 1);
            $decoded = json_decode($fragment, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $sanitized .= $value[$offset++];
                continue;
            }

            $summary = [
                'bytes=' . strlen($fragment),
                'sha256=' . substr(hash('sha256', $fragment), 0, 16),
            ];
            if (is_array($decoded)) {
                foreach (['return_code', 'errcode', 'code', 'status'] as $key) {
                    if (isset($decoded[$key]) && self::isSafeBusinessCode($decoded[$key])) {
                        $summary[] = $key . '=' . (string) $decoded[$key];
                    }
                }
            }

            $sanitized .= '[json-summary:' . implode(' ', $summary) . ']';
            $offset = $end + 1;
        }

        return $sanitized;
    }

    private static function jsonFragmentEnd(string $value, int $start): ?int
    {
        $stack = [];
        $quoted = false;
        $escaped = false;
        $length = strlen($value);

        for ($offset = $start; $offset < $length; ++$offset) {
            $character = $value[$offset];
            if ($quoted) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($character === '\\') {
                    $escaped = true;
                } elseif ($character === '"') {
                    $quoted = false;
                }
                continue;
            }

            if ($character === '"') {
                $quoted = true;
            } elseif ($character === '{' || $character === '[') {
                $stack[] = $character;
            } elseif ($character === '}' || $character === ']') {
                $expected = $character === '}' ? '{' : '[';
                if (array_pop($stack) !== $expected) {
                    return null;
                }
                if ($stack === []) {
                    return $offset;
                }
            }
        }

        return null;
    }

    private static function opaqueSummary(string $type, string $value): string
    {
        return sprintf(
            '[%s:bytes=%d sha256=%s]',
            $type,
            strlen($value),
            substr(hash('sha256', $value), 0, 16)
        );
    }

    private static function isSafeBusinessCode($value): bool
    {
        if (is_int($value) || is_float($value) || is_bool($value)) {
            return true;
        }

        return is_string($value) && preg_match('/^[A-Za-z0-9_.:-]{1,32}$/D', $value) === 1;
    }
}
