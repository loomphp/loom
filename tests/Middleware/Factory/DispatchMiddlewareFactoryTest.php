<?php

declare(strict_types=1);

namespace LoomTest\Middleware\Factory;

use Loom\Middleware\DispatchMiddleware;
use Loom\Middleware\Factory\DispatchMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DispatchMiddlewareFactoryTest extends TestCase
{
    public function testFactoryProducesDispatchMiddleware()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new DispatchMiddlewareFactory();
        $middleware = $factory($container);
        $this->assertInstanceOf(DispatchMiddleware::class, $middleware);
    }
}
