<?php

declare(strict_types=1);

namespace Loom\Middleware\Factory;

use Loom\Middleware\Exception;
use Loom\Middleware\ImplicitHeadMiddleware;
use Loom\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;

class ImplicitHeadMiddlewareFactory
{

    public function __invoke(ContainerInterface $container): ImplicitHeadMiddleware
    {
        if (! $container->has(RouterInterface::class)) {
            throw Exception\MissingDependencyException::dependencyForService(
                RouterInterface::class,
                ImplicitHeadMiddleware::class
            );
        }

        if (! $container->has(StreamInterface::class)) {
            throw Exception\MissingDependencyException::dependencyForService(
                StreamInterface::class,
                ImplicitHeadMiddleware::class
            );
        }

        return new ImplicitHeadMiddleware(
            $container->get(RouterInterface::class),
            $container->get(StreamInterface::class)
        );
    }
}
