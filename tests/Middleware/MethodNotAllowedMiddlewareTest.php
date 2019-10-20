<?php

declare(strict_types=1);

namespace LoomTest\Middleware;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Loom\Middleware\MethodNotAllowedMiddleware;
use Loom\Router\Route;
use Loom\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MethodNotAllowedMiddlewareTest extends TestCase
{
    /** @var RequestHandlerInterface|ObjectProphecy */
    private $handler;

    /** @var MethodNotAllowedMiddleware */
    private $middleware;

    /** @var ServerRequestInterface|ObjectProphecy */
    private $request;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    public function setUp()
    {
        $this->handler = $this->prophesize(RequestHandlerInterface::class);
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $responseFactory = function () {
            return $this->response->reveal();
        };

        $this->middleware = new MethodNotAllowedMiddleware($responseFactory);
    }

    public function testDelegatesToHandlerIfNoRouteResultPresentInRequest()
    {
        $this->request->getAttribute(RouteResult::class)->willReturn(null);
        $this->handler->handle(Argument::that([$this->request, 'reveal']))->will([$this->response, 'reveal']);

        $this->response->withStatus(Argument::any())->shouldNotBeCalled();
        $this->response->withHeader('Allow', Argument::any())->shouldNotBeCalled();

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    public function testDelegatesToHandlerIfRouteResultNotAMethodFailure()
    {
        $result = RouteResult::fromRouteFailure(null);

        $this->request->getAttribute(RouteResult::class)->willReturn($result);
        $this->handler->handle(Argument::that([$this->request, 'reveal']))->will([$this->response, 'reveal']);

        $this->response->withStatus(Argument::any())->shouldNotBeCalled();
        $this->response->withHeader('Allow', Argument::any())->shouldNotBeCalled();

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    public function testReturns405ResponseWithAllowHeaderIfResultDueToMethodFailure()
    {
        $result = RouteResult::fromRouteFailure(['GET', 'POST']);

        $this->request->getAttribute(RouteResult::class)->willReturn($result);
        $this->handler->handle(Argument::that([$this->request, 'reveal']))->shouldNotBeCalled();

        $this->response->withStatus(StatusCode::STATUS_METHOD_NOT_ALLOWED)->will([$this->response, 'reveal']);
        $this->response->withHeader('Allow', 'GET,POST')->will([$this->response, 'reveal']);

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }
}
