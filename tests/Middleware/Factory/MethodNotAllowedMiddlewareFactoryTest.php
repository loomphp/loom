<?php

declare(strict_types=1);

namespace LoomTest\Middleware\Factory;

use Loom\Middleware\Exception\MissingDependencyException;
use Loom\Middleware\Factory\MethodNotAllowedMiddlewareFactory;
use Loom\Middleware\MethodNotAllowedMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class MethodNotAllowedMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var MethodNotAllowedMiddlewareFactory */
    private $factory;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new MethodNotAllowedMiddlewareFactory();
    }

    public function testFactoryRaisesExceptionIfResponseFactoryServiceIsMissing()
    {
        $this->container->has(ResponseInterface::class)->willReturn(false);

        $this->expectException(MissingDependencyException::class);
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryProducesMethodNotAllowedMiddlewareWhenAllDependenciesPresent()
    {
        $factory = function () {
        };

        $this->container->has(ResponseInterface::class)->willReturn(true);
        $this->container->get(ResponseInterface::class)->willReturn($factory);

        $middleware = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(MethodNotAllowedMiddleware::class, $middleware);
    }
}
