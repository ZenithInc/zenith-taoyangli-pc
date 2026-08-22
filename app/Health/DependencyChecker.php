<?php

declare(strict_types=1);

namespace App\Health;

use GuzzleHttp\Client;
use Hyperf\Contract\ConfigInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PDO;
use Redis;
use RuntimeException;
use Throwable;

final class DependencyChecker
{
    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function run(): array
    {
        $results = [];
        $healthy = true;
        foreach ($this->config->get('dependency_probe.checks', []) as $name => $specification) {
            try {
                $this->check($specification);
                $results[$name] = ['status' => 'ok'];
            } catch (Throwable $throwable) {
                $healthy = false;
                $results[$name] = ['status' => 'failed', 'error_type' => get_class($throwable)];
            }
        }

        return ['status' => $healthy ? 'ok' : 'failed', 'checks' => $results];
    }

    public static function isPrivateAddress(string $address): bool
    {
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $numeric = unpack('N', inet_pton($address))[1];
            return ($numeric & 0xff000000) === 0x0a000000
                || ($numeric & 0xfff00000) === 0xac100000
                || ($numeric & 0xffff0000) === 0xc0a80000
                || ($numeric & 0xff000000) === 0x7f000000
                || ($numeric & 0xffff0000) === 0xa9fe0000;
        }

        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $packed = inet_pton($address);
            if ($packed === inet_pton('::1')) {
                return true;
            }
            $first = ord($packed[0]);
            $second = ord($packed[1]);
            return ($first & 0xfe) === 0xfc
                || ($first === 0xfe && ($second & 0xc0) === 0x80);
        }

        return false;
    }

    public static function isPrivateRequest(string $peerAddress, string $forwardedFor): bool
    {
        if (! self::isPrivateAddress($peerAddress)) {
            return false;
        }

        if (trim($forwardedFor) === '') {
            return true;
        }

        foreach (explode(',', $forwardedFor) as $forwardedAddress) {
            if (! self::isPrivateAddress(trim($forwardedAddress))) {
                return false;
            }
        }

        return true;
    }

    private function check(array $specification): void
    {
        $type = (string) ($specification['type'] ?? '');
        if ($type === 'mysql') {
            $this->mysql((string) $specification['host_key'], (string) $specification['prefix']);
            return;
        }
        if ($type === 'redis') {
            $this->redis($specification);
            return;
        }
        if ($type === 'amqp') {
            $this->amqp($specification);
            return;
        }
        if ($type === 'tcp') {
            $this->tcp(
                $this->required((string) $specification['host_key']),
                (int) $this->required((string) $specification['port_key'])
            );
            return;
        }
        if ($type === 'url') {
            $this->url($this->required((string) $specification['url_key']));
            return;
        }

        throw new RuntimeException('Unknown dependency probe type');
    }

    private function mysql(string $hostKey, string $prefix): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->required($hostKey),
            (int) $this->required($prefix . 'PORT'),
            $this->required($prefix . 'DATABASE'),
            $this->required($prefix . 'CHARSET')
        );
        $pdo = new PDO($dsn, $this->required($prefix . 'USERNAME'), $this->required($prefix . 'PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo->query('SELECT 1')->fetchColumn();
    }

    private function redis(array $specification): void
    {
        $prefix = (string) ($specification['prefix'] ?? '');
        $hostKey = (string) ($specification['host_key'] ?? ($prefix . 'HOST'));
        $authKey = (string) ($specification['auth_key'] ?? ($prefix . 'AUTH'));
        $portKey = (string) ($specification['port_key'] ?? ($prefix . 'PORT'));
        $dbKey = (string) ($specification['db_key'] ?? ($prefix . 'DB'));
        $redis = new Redis();
        if (! $redis->connect($this->required($hostKey), (int) $this->required($portKey), 5.0)) {
            throw new RuntimeException('Redis connection failed');
        }
        $auth = getenv($authKey);
        if ($auth !== false && $auth !== '' && ! $redis->auth($auth)) {
            throw new RuntimeException('Redis authentication failed');
        }
        if (! $redis->select((int) $this->required($dbKey))) {
            throw new RuntimeException('Redis database selection failed');
        }
        $pong = $redis->ping();
        if ($pong !== true && $pong !== 'PONG' && $pong !== '+PONG') {
            throw new RuntimeException('Redis ping failed');
        }
        $redis->close();
    }

    private function url(string $url): void
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('Dependency URL is invalid');
        }
        $response = (new Client())->request('HEAD', $url, [
            'connect_timeout' => 5.0,
            'timeout' => 5.0,
            'http_errors' => false,
            'allow_redirects' => false,
        ]);
        if ($response->getStatusCode() >= 500) {
            throw new RuntimeException('HTTP dependency returned server error');
        }
    }

    private function amqp(array $specification): void
    {
        $connection = new AMQPStreamConnection(
            $this->required((string) ($specification['host_key'] ?? 'AMQP_HOST')),
            (int) $this->required((string) ($specification['port_key'] ?? 'AMQP_PORT')),
            $this->required((string) ($specification['user_key'] ?? 'AMQP_USER')),
            $this->required((string) ($specification['password_key'] ?? 'AMQP_PASSWORD')),
            $this->required((string) ($specification['vhost_key'] ?? 'AMQP_VHOST')),
            false,
            'AMQPLAIN',
            null,
            'en_US',
            5.0,
            5.0,
            null,
            false,
            3
        );
        $channel = $connection->channel();
        $channel->close();
        $connection->close();
    }

    private function tcp(string $host, int $port): void
    {
        $socket = @fsockopen($host, $port, $errorCode, $errorMessage, 5.0);
        if (! is_resource($socket)) {
            throw new RuntimeException('TCP connection failed');
        }
        fclose($socket);
    }

    private function required(string $key): string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            throw new RuntimeException('Required dependency configuration is missing');
        }
        return $value;
    }
}
