<?php

declare(strict_types=1);

namespace Loom\Middleware\Factory;

use Loom\Middleware\DispatchMiddleware;
use Psr\Container\ContainerInterface;

class DispatchMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): DispatchMiddleware
    {
        return new DispatchMiddleware();
    }
}
