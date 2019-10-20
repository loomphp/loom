<?php

declare(strict_types=1);

namespace LoomTest\Middleware;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Loom\Middleware\ImplicitOptionsMiddleware;
use Loom\Router\Route;
use Loom\Router\RouteResult;

use function implode;

class ImplicitOptionsMiddlewareTest extends TestCase
{
    /** @var ImplicitOptionsMiddleware */
    private $middleware;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    public function setUp()
    {
        $this->response = $this->prophesize(ResponseInterface::class);
        $responseFactory = function () {
            return $this->response->reveal();
        };

        $this->middleware = new ImplicitOptionsMiddleware($responseFactory);
    }

    public function testNonOptionsRequestInvokesHandler()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn(RequestMethod::METHOD_GET);
        $request->getAttribute(RouteResult::class, false)->shouldNotBeCalled();

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $result = $this->middleware->process($request->reveal(), $handler->reveal());
        $this->assertSame($response, $result);
    }

    public function testMissingRouteResultInvokesHandler()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn(RequestMethod::METHOD_OPTIONS);
        $request->getAttribute(RouteResult::class)->willReturn(null);

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $result = $this->middleware->process($request->reveal(), $handler->reveal());
        $this->assertSame($response, $result);
    }

    public function testReturnsResultOfHandlerWhenRouteSupportsOptionsExplicitly()
    {
        $route = $this->prophesize(Route::class);

        $result = RouteResult::fromRoute($route->reveal());

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn(RequestMethod::METHOD_OPTIONS);
        $request->getAttribute(RouteResult::class)->willReturn($result);

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $result = $this->middleware->process($request->reveal(), $handler->reveal());
        $this->assertSame($response, $result);
    }

    public function testInjectsAllowHeaderInResponseProvidedToConstructorDuringOptionsRequest()
    {
        $allowedMethods = [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST];

        $result = RouteResult::fromRouteFailure($allowedMethods);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn(RequestMethod::METHOD_OPTIONS);
        $request->getAttribute(RouteResult::class)->willReturn($result);

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->shouldNotBeCalled();

        $this->response
            ->withHeader('Allow', implode(',', $allowedMethods))
            ->will([$this->response, 'reveal']);

        $result = $this->middleware->process($request->reveal(), $handler->reveal());
        $this->assertSame($this->response->reveal(), $result);
    }

    public function testReturnsResultOfHandlerWhenRouteNotFound()
    {
        $result = RouteResult::fromRouteFailure(null);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn(RequestMethod::METHOD_OPTIONS);
        $request->getAttribute(RouteResult::class)->willReturn($result);

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $result = $this->middleware->process($request->reveal(), $handler->reveal());
        $this->assertSame($response, $result);
    }
}
