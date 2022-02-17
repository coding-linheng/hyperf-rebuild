<?php

namespace rebuild\Dispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpRequestHandler extends AbstractRequestHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handlerRequest($request);
    }

}