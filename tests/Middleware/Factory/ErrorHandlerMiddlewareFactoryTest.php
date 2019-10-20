<?php

declare(strict_types=1);

namespace LoomTest\Middleware\Factory;

use Closure;
use Loom\Middleware\ErrorHandlerMiddleware;
use Loom\Middleware\Factory\ErrorHandlerMiddlewareFactory;
use Loom\Response\ErrorResponseGenerator;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use TypeError;

class ErrorHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryFailsIfResponseServiceIsMissing()
    {
        $exception = new RuntimeException();
        $this->container->has(ErrorResponseGenerator::class)->willReturn(false);
        $this->container->get(ErrorResponseGenerator::class)->shouldNotBeCalled();
        $this->container->get(ResponseInterface::class)->willThrow($exception);

        $factory = new ErrorHandlerMiddlewareFactory();

        $this->expectException(RuntimeException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryFailsIfResponseServiceReturnsResponse()
    {
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $this->container->has(ErrorResponseGenerator::class)->willReturn(false);
        $this->container->get(ErrorResponseGenerator::class)->shouldNotBeCalled();
        $this->container->get(ResponseInterface::class)->willReturn($response);

        $factory = new ErrorHandlerMiddlewareFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryCreatesHandlerWithGeneratorIfGeneratorServiceAvailable()
    {
        $generator = $this->prophesize(ErrorResponseGenerator::class)->reveal();
        $this->container->has(ErrorResponseGenerator::class)->willReturn(true);
        $this->container->get(ErrorResponseGenerator::class)->willReturn($generator);

        $this->container->get(ResponseInterface::class)->willReturn(function () {
        });

        $factory = new ErrorHandlerMiddlewareFactory();
        $handler = $factory($this->container->reveal());

        $this->assertInstanceOf(ErrorHandlerMiddleware::class, $handler);
        $this->assertAttributeInstanceOf(Closure::class, 'responseFactory', $handler);
        $this->assertAttributeSame($generator, 'responseGenerator', $handler);
    }
}
