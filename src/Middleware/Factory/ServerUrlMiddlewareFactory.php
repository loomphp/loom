<?php

declare(strict_types=1);

namespace Loom\Middleware\Factory;

use Loom\Helper\ServerUrlHelper;
use Loom\Middleware\Exception\MissingHelperException;
use Loom\Middleware\ServerUrlMiddleware;
use Psr\Container\ContainerInterface;

use function sprintf;

class ServerUrlMiddlewareFactory
{

    public function __invoke(ContainerInterface $container) : ServerUrlMiddleware
    {
        if (! $container->has(ServerUrlHelper::class)) {
            throw new MissingHelperException(sprintf(
                '%s requires a %s service at instantiation; none found',
                ServerUrlMiddleware::class,
                ServerUrlHelper::class
            ));
        }

        return new ServerUrlMiddleware($container->get(ServerUrlHelper::class));
    }
}
