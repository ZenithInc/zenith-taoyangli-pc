<?php
declare(strict_types=1);
namespace App\Middleware;

use App\Support\RequestId;
use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class RequestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = RequestId::fromRequest($request);
        $request = $request
            ->withHeader('X-Request-ID', $requestId)
            ->withHeader('requestId', $requestId)
            ->withHeader('qid', $requestId);

        Context::set(ServerRequestInterface::class, $request);
        Context::set('requestId', $requestId);
        Context::set('qid', $requestId);
        Context::set('request_start_time', microtime(true));

        return $handler->handle($request)->withHeader('X-Request-ID', $requestId);
    }
}
