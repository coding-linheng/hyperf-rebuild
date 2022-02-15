<?php

namespace rebuild\HttpServer;

use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Server
{
    public function onRequest(Request $request, Response $response)
    {
        [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);
    }

    protected function initRequestAndResponse(Request $request, Response $response): array
    {
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response);
        Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
        return [$psr7Request, $psr7Response];
    }
}