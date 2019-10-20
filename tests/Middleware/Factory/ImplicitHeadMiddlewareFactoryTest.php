<?php

declare(strict_types=1);

namespace LoomTest\Middleware\Factory;

use Loom\Middleware\Exception\MissingDependencyException;
use Loom\Middleware\Factory\ImplicitHeadMiddlewareFactory;
use Loom\Middleware\ImplicitHeadMiddleware;
use Loom\Router\RouterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;

class ImplicitHeadMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var ImplicitHeadMiddlewareFactory */
    private $factory;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new ImplicitHeadMiddlewareFactory();
    }

    public function testFactoryRaisesExceptionIfRouterInterfaceServiceIsMissing()
    {
        $this->container->has(RouterInterface::class)->willReturn(false);

        $this->expectException(MissingDependencyException::class);
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryRaisesExceptionIfStreamFactoryServiceIsMissing()
    {
        $this->container->has(RouterInterface::class)->willReturn(true);
        $this->container->has(StreamInterface::class)->willReturn(false);

        $this->expectException(MissingDependencyException::class);
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryProducesImplicitHeadMiddlewareWhenAllDependenciesPresent()
    {
        $router = $this->prophesize(RouterInterface::class);
        $streamFactory = function () {
        };

        $this->container->has(RouterInterface::class)->willReturn(true);
        $this->container->has(StreamInterface::class)->willReturn(true);
        $this->container->get(RouterInterface::class)->will([$router, 'reveal']);
        $this->container->get(StreamInterface::class)->willReturn($streamFactory);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(ImplicitHeadMiddleware::class, $middleware);
    }
}
