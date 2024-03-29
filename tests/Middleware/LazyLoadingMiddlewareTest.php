<?php

declare(strict_types=1);

namespace LoomTest\Middleware;

use Loom\ApplicationContainer;
use Loom\Exception\InvalidMiddlewareException;
use Loom\Middleware\LazyLoadingMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LazyLoadingMiddlewareTest extends TestCase
{
    /** @var ApplicationContainer */
    private $container;

    /** @var ServerRequestInterface */
    private $request;

    /** @var RequestHandlerInterface */
    private $handler;

    public function setUp()
    {
        $this->container = $this->prophesize(ApplicationContainer::class);
        $this->request   = $this->prophesize(ServerRequestInterface::class);
        $this->handler   = $this->prophesize(RequestHandlerInterface::class);
    }

    public function buildLazyLoadingMiddleware($middlewareName)
    {
        return new LazyLoadingMiddleware(
            $this->container->reveal(),
            $middlewareName
        );
    }

    public function testProcessesMiddlewarePulledFromContainer()
    {
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $middleware = $this->prophesize(MiddlewareInterface::class);
        $middleware
            ->process(
                $this->request->reveal(),
                $this->handler->reveal()
            )->willReturn($response);

        $this->container->get('foo')->will([$middleware, 'reveal']);

        $lazyloader = $this->buildLazyLoadingMiddleware('foo');
        $this->assertSame(
            $response,
            $lazyloader->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    public function testDoesNotCatchContainerExceptions()
    {
        $exception = new InvalidMiddlewareException();
        $this->container->get('foo')->willThrow($exception);

        $lazyloader = $this->buildLazyLoadingMiddleware('foo');
        $this->expectException(InvalidMiddlewareException::class);
        $lazyloader->process($this->request->reveal(), $this->handler->reveal());
    }
}
