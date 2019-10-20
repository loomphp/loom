<?php

declare(strict_types=1);

namespace Loom;

use Loom\Routing\RouteCollector;
use Loom\Runner\Runner;
use Loom\Router\Route;
use Loom\Seam\SeamMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @var ApplicationStack
     */
    private $stack;

    /**
     * @var SeamMiddlewareInterface
     */
    private $seam;

    /**
     * @var RouteCollector
     */
    private $routes;

    /**
     * @var Runner
     */
    private $runner;

    public function __construct(
        ApplicationStack $stack,
        SeamMiddlewareInterface $seam,
        RouteCollector $routes,
        Runner $runner
    ) {
        $this->stack = $stack;
        $this->seam = $seam;
        $this->routes = $routes;
        $this->runner = $runner;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->seam->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $this->seam->process($request, $handler);
    }

    public function run() : void
    {
        $this->runner->run();
    }

    public function middleware($middleware) : void
    {
        $middleware = $this->stack->prepare($middleware);
        $this->seam->stitch($middleware);
    }

    public function route(string $path, $middleware, array $methods = null, string $name = null) : Route
    {
        return $this->routes->route(
            $path,
            $this->stack->prepare($middleware),
            $methods,
            $name
        );
    }

    public function get(string $path, $middleware, string $name = null) : Route
    {
        return $this->route($path, $middleware, ['GET'], $name);
    }

    public function post(string $path, $middleware, $name = null) : Route
    {
        return $this->route($path, $middleware, ['POST'], $name);
    }

    public function put(string $path, $middleware, string $name = null) : Route
    {
        return $this->route($path, $middleware, ['PUT'], $name);
    }

    public function patch(string $path, $middleware, string $name = null) : Route
    {
        return $this->route($path, $middleware, ['PATCH'], $name);
    }

    public function delete(string $path, $middleware, string $name = null) : Route
    {
        return $this->route($path, $middleware, ['DELETE'], $name);
    }

    public function any(string $path, $middleware, string $name = null) : Route
    {
        return $this->route($path, $middleware, null, $name);
    }

    public function getRoutes() : array
    {
        return $this->routes->getRoutes();
    }
}
