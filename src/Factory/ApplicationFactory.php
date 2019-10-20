<?php

namespace Loom\Factory;

use Loom\Application;
use Loom\ApplicationStack;
use Loom\Middleware\ApplicationMiddleware;
use Loom\Routing\RouteCollector;
use Loom\Runner\Runner;
use Psr\Container\ContainerInterface;

class ApplicationFactory
{
    public function __invoke(ContainerInterface $container): Application
    {
        return new Application(
            $container->get(ApplicationStack::class),
            $container->get(ApplicationMiddleware::class),
            $container->get(RouteCollector::class),
            $container->get(Runner::class)
        );
    }
}
