<?php

declare(strict_types=1);

namespace LoomTest\Middleware;

use Loom\Middleware\CallableMiddleware;
use Loom\Middleware\Exception\MissingResponseException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CallableMiddlewareTest extends TestCase
{
    public function testCallableMiddlewareThatDoesNotProduceAResponseRaisesAnException()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $handler = $this->prophesize(RequestHandlerInterface::class)->reveal();

        $middleware = function ($request, $handler) {
            return 'foo';
        };

        $decorator = new CallableMiddleware($middleware);

        $this->expectException(MissingResponseException::class);
        $this->expectExceptionMessage('failed to produce a response');
        $decorator->process($request, $handler);
    }

    public function testCallableMiddlewareReturningAResponseSucceedsProcessCall()
    {
        $request  = $this->prophesize(ServerRequestInterface::class)->reveal();
        $handler  = $this->prophesize(RequestHandlerInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $middleware = function ($request, $handler) use ($response) {
            return $response;
        };

        $decorator = new CallableMiddleware($middleware);

        $this->assertSame($response, $decorator->process($request, $handler));
    }
}
