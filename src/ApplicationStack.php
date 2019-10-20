<?php

declare(strict_types=1);

namespace Loom;

use Loom\Seam\SeamMiddleware;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_shift;
use function count;
use function is_array;
use function is_callable;
use function is_string;

class ApplicationStack
{
    /**
     * @var ApplicationContainer
     */
    private $container;

    public function __construct(ApplicationContainer $container)
    {
        $this->container = $container;
    }

    public function prepare($middleware) : MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if ($middleware instanceof RequestHandlerInterface) {
            return $this->handler($middleware);
        }

        if (is_callable($middleware)) {
            return $this->callable($middleware);
        }

        if (is_array($middleware)) {
            return $this->stitching(...$middleware);
        }

        if (! is_string($middleware) || $middleware === '') {
            throw Exception\InvalidMiddlewareException::forMiddleware($middleware);
        }

        return $this->lazy($middleware);
    }

    public function callable(callable $middleware) : Middleware\CallableMiddleware
    {
        return new Middleware\CallableMiddleware($middleware);
    }

    public function handler(RequestHandlerInterface $handler) : Middleware\RequestHandlerMiddleware
    {
        return new Middleware\RequestHandlerMiddleware($handler);
    }

    public function lazy(string $middleware) : Middleware\LazyLoadingMiddleware
    {
        return new Middleware\LazyLoadingMiddleware($this->container, $middleware);
    }

    public function stitching(...$middleware) : SeamMiddleware
    {
        if (is_array($middleware[0])
            && count($middleware) === 1
        ) {
            $middleware = array_shift($middleware);
        }

        $seam = new SeamMiddleware();
        foreach ($middleware as $m) {
            $seam->stitch($this->prepare($m));
        }
        return $seam;
    }
}
