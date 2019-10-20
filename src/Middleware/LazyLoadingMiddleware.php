<?php

declare(strict_types=1);

namespace Loom\Middleware;

use Loom\ApplicationContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LazyLoadingMiddleware implements MiddlewareInterface
{
    /**
     * @var ApplicationContainer
     */
    private $container;

    /**
     * @var string
     */
    private $middlewareName;

    public function __construct(ApplicationContainer $container, string $middlewareName)
    {
        $this->container = $container;
        $this->middlewareName = $middlewareName;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middleware = $this->container->get($this->middlewareName);
        return $middleware->process($request, $handler);
    }
}
