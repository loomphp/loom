<?php

declare(strict_types=1);

namespace LoomTest\Middleware\Factory;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Loom\Middleware\Exception\MissingHelperException;
use Loom\Helper\UrlHelper;
use Loom\Middleware\UrlHelperMiddleware;
use Loom\Middleware\Factory\UrlHelperMiddlewareFactory;

class UrlHelperMiddlewareFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function injectContainer($name, $service)
    {
        $service = $service instanceof ObjectProphecy ? $service->reveal() : $service;
        $this->container->has($name)->willReturn(true);
        $this->container->get($name)->willReturn($service);
    }

    public function testFactoryCreatesAndReturnsMiddlewareWhenHelperIsPresentInContainer()
    {
        $helper = $this->prophesize(UrlHelper::class)->reveal();
        $this->injectContainer(UrlHelper::class, $helper);

        $factory = new UrlHelperMiddlewareFactory();
        $middleware = $factory($this->container->reveal());
        $this->assertInstanceOf(UrlHelperMiddleware::class, $middleware);
        $this->assertAttributeSame($helper, 'helper', $middleware);
    }

    public function testFactoryRaisesExceptionWhenContainerDoesNotContainHelper()
    {
        $this->container->has(UrlHelper::class)->willReturn(false);
        $factory = new UrlHelperMiddlewareFactory();
        $this->expectException(MissingHelperException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryUsesUrlHelperServiceProvidedAtInstantiation()
    {
        $helper = $this->prophesize(UrlHelper::class)->reveal();
        $this->injectContainer(MyUrlHelper::class, $helper);
        $factory = new UrlHelperMiddlewareFactory(MyUrlHelper::class);

        $middleware = $factory($this->container->reveal());

        $this->assertInstanceOf(UrlHelperMiddleware::class, $middleware);
        $this->assertAttributeSame($helper, 'helper', $middleware);
    }

    public function testFactoryAllowsSerialization()
    {
        $factory = UrlHelperMiddlewareFactory::__set_state([
            'urlHelperServiceName' => MyUrlHelper::class,
        ]);

        $this->assertInstanceOf(UrlHelperMiddlewareFactory::class, $factory);
        $this->assertAttributeSame(MyUrlHelper::class, 'urlHelperServiceName', $factory);
    }
}
