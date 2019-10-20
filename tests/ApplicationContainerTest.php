<?php

declare(strict_types=1);

namespace LoomTest;

use Loom\ApplicationContainer;
use Loom\Exception;
use Loom\Middleware\DispatchMiddleware;
use Loom\Middleware\RequestHandlerMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApplicationContainerTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $psrContainer;

    /**
     * @var ApplicationContainer
     */
    private $container;

    public function setUp()
    {
        $this->psrContainer = $this->prophesize(ContainerInterface::class);
        $this->container = new ApplicationContainer($this->psrContainer->reveal());
    }

    public function testHasReturnsTrueIfPsrContainerHasService()
    {
        $this->psrContainer->has('foo')->willReturn(true);
        $this->assertTrue($this->container->has('foo'));
    }

    public function testHasReturnsTrueIfPsrContainerDoesNotHaveServiceButClassExists()
    {
        $this->psrContainer->has(__CLASS__)->willReturn(false);
        $this->assertTrue($this->container->has(__CLASS__));
    }

    public function testHasReturnsFalseIfPsrContainerDoesNotHaveServiceAndClassDoesNotExist()
    {
        $this->psrContainer->has('not-a-class')->willReturn(false);
        $this->assertFalse($this->container->has('not-a-class'));
    }

    public function testGetRaisesExceptionIfServiceIsUnknown()
    {
        $this->psrContainer->has('not-a-service')->willReturn(false);

        $this->expectException(Exception\MissingDependencyException::class);
        $this->container->get('not-a-service');
    }

    public function testGetRaisesExceptionIfServiceSpecifiedDoesNotImplementMiddlewareInterface()
    {
        $this->psrContainer->has(__CLASS__)->willReturn(true);
        $this->psrContainer->get(__CLASS__)->willReturn($this);

        $this->expectException(Exception\InvalidMiddlewareException::class);
        $this->container->get(__CLASS__);
    }

    public function testGetRaisesExceptionIfClassSpecifiedDoesNotImplementMiddlewareInterface()
    {
        $this->psrContainer->has(__CLASS__)->willReturn(false);
        $this->psrContainer->get(__CLASS__)->shouldNotBeCalled();

        $this->expectException(Exception\InvalidMiddlewareException::class);
        $this->container->get(__CLASS__);
    }

    public function testGetReturnsServiceFromPsrContainer()
    {
        $middleware = $this->prophesize(MiddlewareInterface::class)->reveal();
        $this->psrContainer->has('middleware-service')->willReturn(true);
        $this->psrContainer->get('middleware-service')->willReturn($middleware);

        $this->assertSame($middleware, $this->container->get('middleware-service'));
    }

    public function testGetReturnsInstantiatedClass()
    {
        $this->psrContainer->has(DispatchMiddleware::class)->willReturn(false);
        $this->psrContainer->get(DispatchMiddleware::class)->shouldNotBeCalled();

        $middleware = $this->container->get(DispatchMiddleware::class);
        $this->assertInstanceOf(DispatchMiddleware::class, $middleware);
    }

    public function testGetWillDecorateARequestHandlerAsMiddleware()
    {
        $handler = $this->prophesize(RequestHandlerInterface::class)->reveal();

        $this->psrContainer->has('AHandlerNotMiddleware')->willReturn(true);
        $this->psrContainer->get('AHandlerNotMiddleware')->willReturn($handler);

        $middleware = $this->container->get('AHandlerNotMiddleware');

        // Test that we get back middleware decorating the handler
        $this->assertInstanceOf(RequestHandlerMiddleware::class, $middleware);
        $this->assertAttributeSame($handler, 'handler', $middleware);
    }

    public function testGetDoesNotCastMiddlewareImplementingRequestHandlerToRequestHandlerMiddleware()
    {
        $pipeline = $this->prophesize(RequestHandlerInterface::class);
        $pipeline->willImplement(MiddlewareInterface::class);

        $this->psrContainer->has('middleware')->willReturn(true);
        $this->psrContainer->get('middleware')->will([$pipeline, 'reveal']);

        $middleware = $this->container->get('middleware');

        $this->assertSame($middleware, $pipeline->reveal());
    }
}
