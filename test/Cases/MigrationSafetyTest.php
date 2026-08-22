<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use App\Controller\Internal\HealthController;
use App\Health\DependencyChecker;
use App\Runtime\RuntimeRole;
use App\Support\LogSanitizer;
use App\Support\RequestId;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class MigrationSafetyTest extends TestCase
{
    public function testRequestIdValidationRejectsInjectionAndOversizedValues(): void
    {
        self::assertTrue(RequestId::isValid('K3S-accept_20260822:01/abc'));
        self::assertTrue(RequestId::isValid(str_repeat('a', 64)));
        self::assertFalse(RequestId::isValid(str_repeat('a', 65)));
        self::assertFalse(RequestId::isValid("safe\r\nforged"));
        self::assertFalse(RequestId::isValid('contains whitespace'));
    }

    public function testLogSanitizerRemovesCredentialsAndHashesIdentity(): void
    {
        $sanitized = LogSanitizer::sanitize([
            'authorization' => 'Bearer fixture-only',
            'phone' => '13800138000',
            'idcard' => '11010519491231002X',
            'requestId' => 'request-safe',
            'order_sn' => 'K3S-ORDER-123',
            'payment_no' => '420000123456789012345678',
            'payload' => ['anything' => 'fixture'],
        ]);
        self::assertSame('[redacted]', $sanitized['authorization']);
        self::assertStringStartsWith('sha256:', $sanitized['phone']);
        self::assertStringStartsWith('sha256:', $sanitized['idcard']);
        self::assertSame('request-safe', $sanitized['requestId']);
        self::assertSame('K3S-ORDER-123', $sanitized['order_sn']);
        self::assertSame('…345678', $sanitized['payment_no']);
        self::assertSame('[omitted]', $sanitized['payload']);

        self::assertSame(
            'https://api.example.test/path',
            LogSanitizer::sanitize(['url' => "https://api.example.test/path?access_token=secret\\r\\nforged"] )['url']
        );
        $record = LogSanitizer::externalLogRecord(
            'third-party request',
            'https://api.example.test/path?access_token=secret',
            ['phone' => '13800138000', 'secret' => 'request-secret'],
            1,
            ['return_code' => 100000, 'access_token' => 'response-secret']
        );
        self::assertSame('https://api.example.test/path', $record['url']);
        self::assertStringNotContainsString('13800138000', $record['require']);
        self::assertStringNotContainsString('request-secret', $record['require']);
        self::assertStringNotContainsString('response-secret', $record['response']);
        self::assertStringContainsString('100000', $record['response']);

        $embedded = LogSanitizer::sanitizeString('pay param:' . json_encode([
            'return_code' => 100014,
            'phone' => 'fixture-phone-value',
            'sign' => 'fixture-sign-value',
            'nested' => ['request_body' => 'fixture-body-value'],
        ], JSON_UNESCAPED_SLASHES));
        self::assertStringContainsString('return_code=100014', $embedded);
        self::assertStringContainsString('[json-summary:', $embedded);
        self::assertStringNotContainsString('fixture-phone-value', $embedded);
        self::assertStringNotContainsString('fixture-sign-value', $embedded);
        self::assertStringNotContainsString('fixture-body-value', $embedded);
        self::assertSame($embedded, LogSanitizer::sanitizeString($embedded));

        $form = LogSanitizer::sanitizeString('request_data:account=fixture-account&sign=fixture-form-sign');
        self::assertStringContainsString('[body:bytes=', $form);
        self::assertStringNotContainsString('fixture-account', $form);
        self::assertStringNotContainsString('fixture-form-sign', $form);
        self::assertSame(
            'callback https://api.example.test/pay',
            LogSanitizer::sanitizeString('callback https://api.example.test/pay?sign=fixture-url-sign')
        );
    }

    public function testDependencyEndpointAddressGuardOnlyAllowsPrivateRanges(): void
    {
        self::assertTrue(DependencyChecker::isPrivateAddress('127.0.0.1'));
        self::assertTrue(DependencyChecker::isPrivateAddress('10.42.1.10'));
        self::assertTrue(DependencyChecker::isPrivateAddress('172.16.1.10'));
        self::assertTrue(DependencyChecker::isPrivateAddress('192.168.1.10'));
        self::assertTrue(DependencyChecker::isPrivateAddress('fd00::1'));
        self::assertTrue(DependencyChecker::isPrivateAddress('169.254.10.20'));
        self::assertFalse(DependencyChecker::isPrivateAddress('8.8.8.8'));
        self::assertFalse(DependencyChecker::isPrivateAddress('0.0.0.0'));
        self::assertFalse(DependencyChecker::isPrivateAddress('203.0.113.10'));
        self::assertFalse(DependencyChecker::isPrivateAddress('2001:4860:4860::8888'));
        self::assertFalse(DependencyChecker::isPrivateAddress('not-an-address'));
        self::assertTrue(DependencyChecker::isPrivateRequest('10.42.0.1', '192.168.1.20, 10.0.0.1'));
        self::assertTrue(DependencyChecker::isPrivateRequest('10.42.0.1', ''));
        self::assertFalse(DependencyChecker::isPrivateRequest('10.42.0.1', '8.8.8.8, 10.0.0.1'));
        self::assertFalse(DependencyChecker::isPrivateRequest('10.42.0.1', '10.0.0.1, 8.8.8.8'));
        self::assertFalse(DependencyChecker::isPrivateRequest('8.8.8.8', '192.168.1.20'));
    }

    public function testModelCacheRedisAuthUsesItsDedicatedEnvironmentKey(): void
    {
        $previousHost = getenv('MODEL_CACHE_REDIS_HOST');
        $previousAuth = getenv('MODEL_CACHE_REDIS_AUTH');
        try {
            putenv('MODEL_CACHE_REDIS_HOST=host-fixture');
            putenv('MODEL_CACHE_REDIS_AUTH=auth-fixture');
            $config = require BASE_PATH . '/config/autoload/redis.php';
            self::assertSame('host-fixture', $config['model_cache']['host']);
            self::assertSame('auth-fixture', $config['model_cache']['auth']);
        } finally {
            $previousHost === false ? putenv('MODEL_CACHE_REDIS_HOST') : putenv('MODEL_CACHE_REDIS_HOST=' . $previousHost);
            $previousAuth === false ? putenv('MODEL_CACHE_REDIS_AUTH') : putenv('MODEL_CACHE_REDIS_AUTH=' . $previousAuth);
        }
    }
    public function testPcNeverStartsWorkerOrSchedulerProcesses(): void
    {
        $previous = getenv('APP_ROLE');
        try {
            foreach ([RuntimeRole::HTTP, RuntimeRole::WORKER, RuntimeRole::SCHEDULER] as $role) {
                putenv('APP_ROLE=' . $role);
                self::assertSame([], require BASE_PATH . '/config/autoload/processes.php');
                self::assertFalse((require BASE_PATH . '/config/autoload/crontab.php')['enable']);
            }
        } finally {
            $previous === false ? putenv('APP_ROLE') : putenv('APP_ROLE=' . $previous);
        }
    }

    public function testHealthRoutesFollowUnknownRoutePathWithoutInternalMarker(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getHeaderLine')
            ->with('X-Taoyangli-Internal-Probe')
            ->willReturn('');
        $controller = new HealthController(
            $this->createMock(HttpResponse::class),
            $request,
            new DependencyChecker($this->createMock(ConfigInterface::class))
        );

        $this->expectException(NotFoundHttpException::class);
        $controller->live();
    }
}
