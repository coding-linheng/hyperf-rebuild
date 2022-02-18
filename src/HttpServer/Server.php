<?php

namespace rebuild\HttpServer;

use FastRoute\Dispatcher;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use rebuild\Config\ConfigFactory;
use rebuild\Dispatcher\HttpRequestHandler;
use rebuild\HttpServer\Contract\CoreMiddlewareInterface;
use rebuild\HttpServer\Router\Dispatched;
use rebuild\HttpServer\Router\DispatcherFactory;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Server
{

    protected Dispatcher $dispatcher;

    protected CoreMiddlewareInterface $coreMiddleware;
    protected array $globalMiddlewares;

    protected DispatcherFactory $dispatcherFactory;

    /**
     * 路由调度
     * @param DispatcherFactory $dispatcherFactory
     */
    public function __construct(DispatcherFactory $dispatcherFactory)
    {
        $this->dispatcherFactory = $dispatcherFactory;
        $this->dispatcher        = $this->dispatcherFactory->getDispatcher('http');
    }

    public function initCoreMiddleware()
    {
        $this->coreMiddleware    = new CoreMiddleware($this->dispatcherFactory);
        $this->globalMiddlewares = (new ConfigFactory)()->get('middlewares');
    }

    /**
     * 服务回调
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function onRequest(Request $request, Response $response)
    {
        /** @var ServerRequestInterface $psr7Request */
        /** @var ResponseInterface $psr7Response */
        [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);

        //重新调度
        $psr7Request = $this->coreMiddleware->dispatch($psr7Request);

        $method = $psr7Request->getMethod();
        $path   = $psr7Request->getUri()->getPath();

        //获取所有路由配置
        $middlewares = $this->globalMiddlewares;
        $dispatched  = $psr7Request->getAttribute(Dispatched::class);
        //所属新调度/找到路由
        if($dispatched instanceof Dispatched && $dispatched->isFound()) {
            //合并方法注册路由
            $registeredMiddlewares = MiddlewareManager::get($path, $method);
            $middlewares           = array_merge($middlewares, $registeredMiddlewares);
        }
        $requestHandler = new HttpRequestHandler($this->coreMiddleware, $middlewares);
        $psr7Response   = $requestHandler->handle($psr7Request);

        $response->end($psr7Response);
    }

    /**
     * 初始化swoole请求 转化为psr7请求和响应
     * @param Request $request
     * @param Response $response
     * @return array
     */
    protected function initRequestAndResponse(Request $request, Response $response): array
    {
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response);
        Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
        return [$psr7Request, $psr7Response];
    }
}