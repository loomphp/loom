<?php

declare(strict_types=1);

namespace LoomTest\Middleware;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Loom\Helper\ServerUrlHelper;
use Loom\Middleware\ServerUrlMiddleware;

class ServerUrlMiddlewareTest extends TestCase
{
    public function testMiddlewareInjectsHelperWithUri()
    {
        $uri = $this->prophesize(UriInterface::class);
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getUri()->willReturn($uri->reveal());

        $helper = new ServerUrlHelper();
        $middleware = new ServerUrlMiddleware($helper);

        $invoked = false;

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(RequestInterface::class))->will(function ($req) use (&$invoked) {
            $invoked = true;

            return new Response();
        });

        $test = $middleware->process($request->reveal(), $handler->reveal());
        //$this->assertSame($response->reveal(), $test, 'Unexpected return value from middleware');
        $this->assertTrue($invoked, 'next() was not invoked');

        $this->assertAttributeSame(
            $uri->reveal(),
            'uri',
            $helper,
            'Helper was not injected with URI from request'
        );
    }
}
