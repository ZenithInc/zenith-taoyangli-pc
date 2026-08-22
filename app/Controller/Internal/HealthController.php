<?php

declare(strict_types=1);

namespace App\Controller\Internal;

use App\Health\DependencyChecker;
use App\Runtime\RuntimeRole;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HealthController
{
    private HttpResponse $response;

    private ServerRequestInterface $request;

    private DependencyChecker $dependencyChecker;

    public function __construct(
        HttpResponse $response,
        ServerRequestInterface $request,
        DependencyChecker $dependencyChecker
    ) {
        $this->response = $response;
        $this->request = $request;
        $this->dependencyChecker = $dependencyChecker;
    }

    public function live(): ResponseInterface
    {
        $this->requireInternalProbe();

        return $this->response->json(['status' => 'ok']);
    }

    public function ready(): ResponseInterface
    {
        $this->requireInternalProbe();

        return $this->response->json(['status' => 'ok', 'role' => RuntimeRole::current()]);
    }

    public function dependencies(): ResponseInterface
    {
        $this->requireInternalProbe();

        $remoteAddress = (string) ($this->request->getServerParams()['remote_addr'] ?? '');
        $forwardedFor = $this->request->getHeaderLine('X-Forwarded-For');
        if (! DependencyChecker::isPrivateRequest($remoteAddress, $forwardedFor)) {
            return $this->response->json(['status' => 'not_found'])->withStatus(404);
        }

        $result = $this->dependencyChecker->run();
        return $this->response->json($result)->withStatus($result['status'] === 'ok' ? 200 : 503);
    }

    private function requireInternalProbe(): void
    {
        if ($this->request->getHeaderLine('X-Taoyangli-Internal-Probe') !== '1') {
            throw new NotFoundHttpException();
        }
    }
}
