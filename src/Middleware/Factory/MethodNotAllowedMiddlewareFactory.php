<?php

declare(strict_types=1);

namespace Loom\Middleware\Factory;

use Loom\Middleware\Exception;
use Loom\Middleware\MethodNotAllowedMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class MethodNotAllowedMiddlewareFactory
{

    public function __invoke(ContainerInterface $container): MethodNotAllowedMiddleware
    {
        if (! $container->has(ResponseInterface::class)) {
            throw Exception\MissingDependencyException::dependencyForService(
                ResponseInterface::class,
                MethodNotAllowedMiddleware::class
            );
        }

        return new MethodNotAllowedMiddleware(
            $container->get(ResponseInterface::class)
        );
    }
}
