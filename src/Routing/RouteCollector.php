<?php

declare(strict_types=1);

namespace Loom\Routing;

use Loom\Router\Route;
use Loom\Router\RouterInterface;
use Psr\Http\Server\MiddlewareInterface;

use function array_filter;
use function array_reduce;

class RouteCollector
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * List of all routes registered directly with the application.
     *
     * @var Route[]
     */
    private $routes = [];

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function route(
        string $path,
        MiddlewareInterface $middleware,
        array $methods = null,
        string $name = null
    ): Route {
        $this->checkForDuplicateRoute($path, $methods);

        $methods = null === $methods ? null : $methods;
        $route = new Route($path, $middleware, $methods, $name);

        $this->routes[] = $route;
        $this->router->addRoute($route);

        return $route;
    }

    public function get(string $path, MiddlewareInterface $middleware, string $name = null): Route
    {
        return $this->route($path, $middleware, ['GET'], $name);
    }

    public function post(string $path, MiddlewareInterface $middleware, string $name = null): Route
    {
        return $this->route($path, $middleware, ['POST'], $name);
    }

    public function put(string $path, MiddlewareInterface $middleware, string $name = null): Route
    {
        return $this->route($path, $middleware, ['PUT'], $name);
    }

    public function patch(string $path, MiddlewareInterface $middleware, string $name = null): Route
    {
        return $this->route($path, $middleware, ['PATCH'], $name);
    }

    public function delete(string $path, MiddlewareInterface $middleware, string $name = null): Route
    {
        return $this->route($path, $middleware, ['DELETE'], $name);
    }

    public function any(string $path, MiddlewareInterface $middleware, string $name = null): Route
    {
        return $this->route($path, $middleware, null, $name);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    private function checkForDuplicateRoute(string $path, array $methods = null): void
    {
        if (null === $methods) {
            $methods = null;
        }

        $matches = array_filter($this->routes, function (Route $route) use ($path, $methods) {
            if ($path !== $route->getPath()) {
                return false;
            }

            if ($methods === null) {
                return true;
            }

            return array_reduce($methods, function ($carry, $method) use ($route) {
                return ($carry || $route->isAllowedMethod($method));
            }, false);
        });

        if (! empty($matches)) {
            $match = reset($matches);
            $allowedMethods = $match->getMethods() ?: ['(any)'];
            $name = $match->getName();
            throw new Exception\DuplicateRouteException(sprintf(
                'Duplicate route detected; path "%s" answering to methods [%s]%s',
                $match->getPath(),
                implode(',', $allowedMethods),
                $name ? sprintf(', with name "%s"', $name) : ''
            ));
        }
    }
}
