<?php

declare(strict_types=1);

namespace Loom\Middleware\Factory;

use Loom\Middleware\Exception;
use Loom\Middleware\ImplicitOptionsMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ImplicitOptionsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ImplicitOptionsMiddleware
    {
        if (! $container->has(ResponseInterface::class)) {
            throw Exception\MissingDependencyException::dependencyForService(
                ResponseInterface::class,
                ImplicitOptionsMiddleware::class
            );
        }

        return new ImplicitOptionsMiddleware(
            $container->get(ResponseInterface::class)
        );
    }
}
