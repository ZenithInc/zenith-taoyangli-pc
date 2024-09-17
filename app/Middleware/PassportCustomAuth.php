<?php
declare(strict_types=1);
namespace App\Middleware;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PassportCustomAuth implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var HttpResponse
     */
    protected $response;
    public function __construct(ContainerInterface $container, RequestInterface $request, HttpResponse $response)
    {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * 不带签名验证
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $secretToken = $this->request->header('secret-token') ?? '';
        $signToken = $this->request->header('sign-token') ?? '';
        $appId = $this->request->header('app-id') ?? '';
        if ( !empty($secretToken) && !empty($signToken) && ( !empty($appId) && $appId === config('client_app_id'))) {
            if (strpos($secretToken, '|')) {
                [$secret, $timestamp] = explode('|', $secretToken);
                //file_put_contents(storage_path('logs/sign.log'),'secretToken='.$secretToken.'___secret='.$secret.'__timestamp='.$timestamp.'__client_app_secret='.config('app.client_app_secret').'__md5(client_app_secret+timestamp)='.md5(config('app.client_app_secret') . $timestamp)."__4"."\r\n",FILE_APPEND);

                //               if ($signToken === $sign && $secret === md5(config('app.client_app_secret') . $timestamp)) {
                if ($secret === md5(config('client_app_secret').$timestamp)) {
                    return $handler->handle($request);
                }
            }
        }
        return $this->response->json(
            [
                'return_code' => 100010,
                'msg' => '认证失败',
                'data' => [],
            ]
        );
    }
}
