<?php

declare(strict_types=1);

namespace Loom\Middleware\Factory;

use Loom\Middleware\Exception;
use Loom\Middleware\RouteMiddleware;
use Loom\Router\RouterInterface;
use Psr\Container\ContainerInterface;

class RouteMiddlewareFactory
{
    /** @var string */
    private $routerServiceName;

    public static function __set_state(array $data): self
    {
        return new self(
            $data['routerServiceName'] ?? RouterInterface::class
        );
    }

    public function __construct(string $routerServiceName = RouterInterface::class)
    {
        $this->routerServiceName = $routerServiceName;
    }

    public function __invoke(ContainerInterface $container): RouteMiddleware
    {
        if (! $container->has($this->routerServiceName)) {
            throw Exception\MissingDependencyException::dependencyForService(
                $this->routerServiceName,
                RouteMiddleware::class
            );
        }

        return new RouteMiddleware($container->get($this->routerServiceName));
    }
}
