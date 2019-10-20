<?php

declare(strict_types=1);

namespace Loom\Middleware\Factory;

use Loom\Seam\SeamMiddleware;
use Loom\Seam\SeamMiddlewareInterface;
use Psr\Container\ContainerInterface;

class ApplicationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : SeamMiddlewareInterface
    {
        return new SeamMiddleware();
    }
}
