<?php

declare(strict_types=1);

namespace LoomTest\Helpe\Factory;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Loom\Middleware\Exception\MissingHelperException;
use Loom\Helper\ServerUrlHelper;
use Loom\Middleware\ServerUrlMiddleware;
use Loom\Middleware\Factory\ServerUrlMiddlewareFactory;

class ServerUrlMiddlewareFactoryTest extends TestCase
{
    public function testCreatesAndReturnsMiddlewareWhenHelperIsPresentInContainer()
    {
        $helper = $this->prophesize(ServerUrlHelper::class);
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ServerUrlHelper::class)->willReturn(true);
        $container->get(ServerUrlHelper::class)->willReturn($helper->reveal());

        $factory = new ServerUrlMiddlewareFactory();
        $middleware = $factory($container->reveal());
        $this->assertInstanceOf(ServerUrlMiddleware::class, $middleware);
    }

    public function testRaisesExceptionWhenContainerDoesNotContainHelper()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ServerUrlHelper::class)->willReturn(false);

        $factory = new ServerUrlMiddlewareFactory();

        $this->expectException(MissingHelperException::class);
        $factory($container->reveal());
    }
}
