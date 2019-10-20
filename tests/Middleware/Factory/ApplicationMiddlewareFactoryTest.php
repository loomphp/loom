<?php

declare(strict_types=1);

namespace LoomTest\Helpe\Factory;

use Loom\Middleware\Factory\ApplicationMiddlewareFactory;
use Loom\Seam\SeamMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ApplicationMiddlewareFactoryTest extends TestCase
{
    public function testCreatesAndReturnsSeamMiddleware()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $factory = new ApplicationMiddlewareFactory();
        $middleware = $factory($container->reveal());
        $this->assertInstanceOf(SeamMiddleware::class, $middleware);
    }
}
