<?php

declare(strict_types=1);

namespace Loom\Routing\Factory;

use Loom\Router\Router;
use Psr\Container\ContainerInterface;

class RouterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config')
            ? $container->get('config')
            : [];

        $config = $config['router']['fastroute'] ?? [];

        return new Router(null, null, $config);
    }
}
