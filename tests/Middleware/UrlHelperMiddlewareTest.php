<?php

declare(strict_types=1);

namespace LoomTest\Middleware;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Loom\Helper\UrlHelper;
use Loom\Middleware\UrlHelperMiddleware;
use Loom\Router\RouteResult;

class UrlHelperMiddlewareTest extends TestCase
{
    /**
     * @var UrlHelper|ObjectProphecy
     */
    private $helper;

    public function setUp()
    {
        $this->helper = $this->prophesize(UrlHelper::class);
    }

    public function createMiddleware()
    {
        return new UrlHelperMiddleware($this->helper->reveal());
    }

    public function testInvocationInjectsHelperWithRouteResultWhenPresentInRequest()
    {
        $response = $this->prophesize(ResponseInterface::class);

        $routeResult = $this->prophesize(RouteResult::class)->reveal();
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn($routeResult);
        $this->helper->setRouteResult($routeResult)->shouldBeCalled();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(ServerRequestInterface::class))->will([$response, 'reveal']);

        $middleware = $this->createMiddleware();
        $this->assertSame($response->reveal(), $middleware->process(
            $request->reveal(),
            $handler->reveal()
        ));
    }

    public function testInvocationDoesNotInjectHelperWithRouteResultWhenAbsentInRequest()
    {
        $response = $this->prophesize(ResponseInterface::class);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn(false);
        $this->helper->setRouteResult(Argument::any())->shouldNotBeCalled();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::type(ServerRequestInterface::class))->will([$response, 'reveal']);

        $middleware = $this->createMiddleware();
        $this->assertSame($response->reveal(), $middleware->process(
            $request->reveal(),
            $handler->reveal()
        ));
    }
}
