<?php

declare(strict_types=1);

namespace Loom\Factory;

use Psr\Container\ContainerInterface;
use Zend\Diactoros\ServerRequestFactory;

class ServerRequestFactoryFactory
{
    public function __invoke(ContainerInterface $container) : callable
    {
        return function () {
            return ServerRequestFactory::fromGlobals();
        };
    }
}
