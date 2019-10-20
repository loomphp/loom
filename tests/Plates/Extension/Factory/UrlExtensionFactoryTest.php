<?php

declare(strict_types=1);

namespace LoomTest\Plates\Extension\Factory;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ProphecyInterface;
use Psr\Container\ContainerInterface;
use Loom\Helper\ServerUrlHelper;
use Loom\Helper\UrlHelper;
use Loom\Plates\Exception\MissingHelperException;
use Loom\Plates\Extension\UrlExtension;
use Loom\Plates\Extension\Factory\UrlExtensionFactory;

class UrlExtensionFactoryTest extends TestCase
{
    /** @var ContainerInterface|ProphecyInterface */
    private $container;

    /** @var UrlHelper|ProphecyInterface */
    private $urlHelper;

    /** @var ServerUrlHelper|ProphecyInterface */
    private $serverUrlHelper;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->urlHelper = $this->prophesize(UrlHelper::class);
        $this->serverUrlHelper = $this->prophesize(ServerUrlHelper::class);
    }

    public function testFactoryReturnsUrlExtensionInstanceWhenHelpersArePresent()
    {
        $this->container->has(UrlHelper::class)->willReturn(true);
        $this->container->get(UrlHelper::class)->willReturn($this->urlHelper->reveal());
        $this->container->has(ServerUrlHelper::class)->willReturn(true);
        $this->container->get(ServerUrlHelper::class)->willReturn($this->serverUrlHelper->reveal());

        $factory = new UrlExtensionFactory();
        $extension = $factory($this->container->reveal());
        $this->assertInstanceOf(UrlExtension::class, $extension);

        $this->assertAttributeSame($this->urlHelper->reveal(), 'urlHelper', $extension);
        $this->assertAttributeSame($this->serverUrlHelper->reveal(), 'serverUrlHelper', $extension);
    }

    public function testFactoryRaisesExceptionIfUrlHelperIsMissing()
    {
        $this->container->has(UrlHelper::class)->willReturn(false);
        $this->container->get(UrlHelper::class)->shouldNotBeCalled();
        $this->container->has(ServerUrlHelper::class)->shouldNotBeCalled();
        $this->container->get(ServerUrlHelper::class)->shouldNotBeCalled();

        $factory = new UrlExtensionFactory();

        $this->expectException(MissingHelperException::class);
        $this->expectExceptionMessage(UrlHelper::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionIfServerUrlHelperIsMissing()
    {
        $this->container->has(UrlHelper::class)->willReturn(true);
        $this->container->get(UrlHelper::class)->shouldNotBeCalled();
        $this->container->has(ServerUrlHelper::class)->willReturn(false);
        $this->container->get(ServerUrlHelper::class)->shouldNotBeCalled();

        $factory = new UrlExtensionFactory();

        $this->expectException(MissingHelperException::class);
        $this->expectExceptionMessage(ServerUrlHelper::class);
        $factory($this->container->reveal());
    }
}
