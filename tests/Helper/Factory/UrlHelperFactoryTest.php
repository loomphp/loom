<?php

declare(strict_types=1);

namespace LoomTest\Helper\Factory;

use Loom\Helper\Factory\UrlHelperFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Loom\Helper\Exception\MissingRouterException;
use Loom\Helper\UrlHelper;
use Loom\Router\RouterInterface;

class UrlHelperFactoryTest extends TestCase
{
    /**
     * @var RouterInterface|ObjectProphecy
     */
    private $router;

    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $container;

    /**
     * @var UrlHelperFactory
     */
    private $factory;

    public function setUp()
    {
        $this->router = $this->prophesize(RouterInterface::class);
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->factory = new UrlHelperFactory();
    }

    public function injectContainerService($name, $service)
    {
        $this->container->has($name)->willReturn(true);
        $this->container->get($name)->willReturn($service);
    }

    public function testFactoryReturnsHelperWithRouterInjected()
    {
        $this->injectContainerService(RouterInterface::class, $this->router->reveal());

        $helper = $this->factory->__invoke($this->container->reveal());
        $this->assertInstanceOf(UrlHelper::class, $helper);
        $this->assertAttributeSame($this->router->reveal(), 'router', $helper);
        return $helper;
    }

    /**
     * @depends testFactoryReturnsHelperWithRouterInjected
     */
    public function testHelperUsesDefaultBasePathWhenNoneProvidedAtInstantiation(UrlHelper $helper)
    {
        $this->assertEquals('/', $helper->getBasePath());
    }

    public function testFactoryRaisesExceptionWhenRouterIsNotPresentInContainer()
    {
        $this->expectException(MissingRouterException::class);
        $this->factory->__invoke($this->container->reveal());
    }

    public function testFactoryUsesBasePathAndRouterServiceProvidedAtInstantiation()
    {
        $this->injectContainerService(Router::class, $this->router->reveal());
        $factory = new UrlHelperFactory('/api', Router::class);

        $helper = $factory($this->container->reveal());

        $this->assertInstanceOf(UrlHelper::class, $helper);
        $this->assertAttributeSame($this->router->reveal(), 'router', $helper);
        $this->assertEquals('/api', $helper->getBasePath());
    }

    public function testFactoryAllowsSerialization()
    {
        $factory = UrlHelperFactory::__set_state([
            'basePath' => '/api',
            'routerServiceName' => Router::class,
        ]);

        $this->assertInstanceOf(UrlHelperFactory::class, $factory);
        $this->assertAttributeSame('/api', 'basePath', $factory);
        $this->assertAttributeSame(Router::class, 'routerServiceName', $factory);
    }
}
