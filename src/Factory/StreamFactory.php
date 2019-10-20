<?php

declare(strict_types=1);

namespace Loom\Factory;

use Psr\Container\ContainerInterface;
use Zend\Diactoros\Stream;

class StreamFactory
{
    public function __invoke(ContainerInterface $container) : callable
    {
        return function () : Stream {
            return new Stream('php://temp', 'wb+');
        };
    }
}
