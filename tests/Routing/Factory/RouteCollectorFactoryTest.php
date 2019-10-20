<?php

declare(strict_types=1);

namespace LoomTest\Routing;

use Loom\Routing\Exception\MissingDependencyException;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Loom\Routing\RouteCollector;
use Loom\Routing\Factory\RouteCollectorFactory;
use Loom\Router\RouterInterface;

class RouteCollectorFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var RouteCollectorFactory */
    private $factory;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new RouteCollectorFactory();
    }

    public function testFactoryRaisesExceptionIfRouterServiceIsMissing()
    {
        $this->container->has(RouterInterface::class)->willReturn(false);

        $this->expectException(MissingDependencyException::class);
        $this->expectExceptionMessage(RouteCollector::class);
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryProducesRouteCollectorWhenAllDependenciesPresent()
    {
        $router = $this->prophesize(RouterInterface::class)->reveal();
        $this->container->has(RouterInterface::class)->willReturn(true);
        $this->container->get(RouterInterface::class)->willReturn($router);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(RouteCollector::class, $middleware);
    }
}
