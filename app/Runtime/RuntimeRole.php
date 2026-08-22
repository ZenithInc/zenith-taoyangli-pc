<?php

declare(strict_types=1);

namespace App\Runtime;

use InvalidArgumentException;

final class RuntimeRole
{
    public const HTTP = 'http';
    public const WORKER = 'worker';
    public const SCHEDULER = 'scheduler';

    public static function current(): string
    {
        $role = strtolower(trim((string) (getenv('APP_ROLE') ?: self::HTTP)));
        if (! in_array($role, self::all(), true)) {
            throw new InvalidArgumentException('APP_ROLE must be http, worker, or scheduler');
        }

        return $role;
    }

    public static function is(string $expected): bool
    {
        return self::current() === $expected;
    }

    public static function all(): array
    {
        return [self::HTTP, self::WORKER, self::SCHEDULER];
    }
}
