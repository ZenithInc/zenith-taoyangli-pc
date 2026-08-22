<?php

declare(strict_types=1);

namespace App\Support;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class RequestId
{
    public const MAX_LENGTH = 64;

    private const HEADER_NAMES = ['X-Request-ID', 'requestId', 'qid'];

    public static function fromRequest(ServerRequestInterface $request): string
    {
        foreach (self::HEADER_NAMES as $headerName) {
            $candidate = trim($request->getHeaderLine($headerName));
            if (self::isValid($candidate)) {
                return $candidate;
            }
        }

        return self::generate();
    }

    public static function isValid(string $value): bool
    {
        if ($value === '' || strlen($value) > self::MAX_LENGTH) {
            return false;
        }

        if (str_contains($value, "\r") || str_contains($value, "\n")) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._:\/-]{0,63}$/D', $value) === 1;
    }

    public static function generate(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Throwable $throwable) {
            return str_replace('.', '', uniqid('', true));
        }
    }
}
