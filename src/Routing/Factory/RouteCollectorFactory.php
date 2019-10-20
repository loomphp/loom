<?php

declare(strict_types=1);

namespace Loom\Routing\Factory;

use Loom\Routing\Exception\MissingDependencyException;
use Loom\Routing\RouteCollector;
use Loom\Router\RouterInterface;
use Psr\Container\ContainerInterface;

class RouteCollectorFactory
{

    public function __invoke(ContainerInterface $container) : RouteCollector
    {
        if (! $container->has(RouterInterface::class)) {
            throw MissingDependencyException::dependencyForService(
                RouterInterface::class,
                RouteCollector::class
            );
        }

        return new RouteCollector($container->get(RouterInterface::class));
    }
}
