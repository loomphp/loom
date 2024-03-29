<?php

declare(strict_types=1);

namespace Loom\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestHandlerMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @var RequestHandlerInterface
     */
    private $handler;

    public function __construct(RequestHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->handler->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $this->handler->handle($request);
    }
}
