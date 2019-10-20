<?php

declare(strict_types=1);

namespace Loom\Factory;

use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response;

class ResponseFactory
{
    public function __invoke(ContainerInterface $container): callable
    {
        return function (): Response {
            return new Response();
        };
    }
}
