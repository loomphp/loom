<?php

declare(strict_types=1);

namespace Loom\Runner\Factory;

use Loom\Middleware\ApplicationMiddleware;
use Loom\Response\ServerRequestErrorResponseGenerator;
use Loom\Runner\Emitter\EmitterInterface;
use Loom\Runner\Runner;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class RunnerFactory
{
    public function __invoke(ContainerInterface $container) : Runner
    {
        return new Runner(
            $container->get(ApplicationMiddleware::class),
            $container->get(EmitterInterface::class),
            $container->get(ServerRequestInterface::class),
            $container->get(ServerRequestErrorResponseGenerator::class)
        );
    }
}
