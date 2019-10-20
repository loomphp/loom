<?php

declare(strict_types=1);

namespace LoomTest\Middleware;

use Loom\Middleware\RequestHandlerMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlerMiddlewareTest extends TestCase
{
    public function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $this->response = $this->prophesize(ResponseInterface::class)->reveal();

        $this->handler = $this->prophesize(RequestHandlerInterface::class);
        $this->handler->handle($this->request)->willReturn($this->response);

        $this->middleware = new RequestHandlerMiddleware($this->handler->reveal());
    }

    public function testDecoratesHandlerAsMiddleware()
    {
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::any())->shouldNotBeCalled();

        $this->assertSame(
            $this->response,
            $this->middleware->process($this->request, $handler->reveal())
        );
    }

    public function testDecoratesHandlerAsHandler()
    {
        $this->assertSame(
            $this->response,
            $this->middleware->handle($this->request)
        );
    }
}
