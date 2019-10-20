<?php

declare(strict_types=1);

namespace LoomTest;

use Loom\ApplicationStack;
use Loom\Seam\SeamMiddleware;
use Loom\Seam\SeamMiddlewareInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TypeError;
use Loom\Application;
use Loom\Router\Route;
use Loom\Routing\RouteCollector;
use Loom\Runner\Runner;

use function array_unshift;
use function sprintf;
use function strtoupper;

class ApplicationTest extends TestCase
{
    public function setUp()
    {
        $this->middleware = $this->prophesize(ApplicationStack::class);
        $this->seam = $this->prophesize(SeamMiddlewareInterface::class);
        $this->routes = $this->prophesize(RouteCollector::class);
        $this->runner = $this->prophesize(Runner::class);

        $this->app = new Application(
            $this->middleware->reveal(),
            $this->seam->reveal(),
            $this->routes->reveal(),
            $this->runner->reveal()
        );
    }

    public function createMockMiddleware()
    {
        return $this->prophesize(MiddlewareInterface::class)->reveal();
    }

    public function testHandleProxiesToSeamToHandle()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $this->seam->handle($request)->willReturn($response);

        $this->assertSame($response, $this->app->handle($request));
    }

    public function testProcessProxiesToSeamToProcess()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $handler = $this->prophesize(RequestHandlerInterface::class)->reveal();

        $this->seam->process($request, $handler)->willReturn($response);

        $this->assertSame($response, $this->app->process($request, $handler));
    }

    public function testRunProxiesToRunner()
    {
        $this->runner->run(null)->shouldBeCalled();
        $this->assertNull($this->app->run());
    }

    public function validMiddleware() : iterable
    {
        // @codingStandardsIgnoreStart
        yield 'string'   => ['service'];
        yield 'array'    => [['middleware', 'service']];
        yield 'callable' => [function ($request, $response) {}];
        yield 'instance' => [new SeamMiddleware()];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider validMiddleware
     */
    public function testStitchCanAcceptSingleMiddlewareArgument($middleware)
    {
        $preparedMiddleware = $this->createMockMiddleware();
        $this->middleware
            ->prepare($middleware)
            ->willReturn($preparedMiddleware);

        $this->seam
            ->stitch(Argument::that(function ($test) use ($preparedMiddleware) {
                Assert::assertSame($preparedMiddleware, $test);
                return $test;
            }))
            ->shouldBeCalled();

        $this->assertNull($this->app->middleware($middleware));
    }

    /**
     * @dataProvider validMiddleware
     */
    public function testRouteAcceptsPathAndMiddlewareOnly($middleware)
    {
        $preparedMiddleware = $this->createMockMiddleware();

        $this->middleware
            ->prepare($middleware)
            ->willReturn($preparedMiddleware);

        $route = $this->prophesize(Route::class)->reveal();

        $this->routes
            ->route(
                '/foo',
                $preparedMiddleware,
                null,
                null
            )
            ->willReturn($route);

        $this->assertSame($route, $this->app->route('/foo', $middleware));
    }

    /**
     * @dataProvider validMiddleware
     */
    public function testRouteAcceptsPathMiddlewareAndMethodsOnly($middleware)
    {
        $preparedMiddleware = $this->createMockMiddleware();

        $this->middleware
            ->prepare($middleware)
            ->willReturn($preparedMiddleware);

        $route = $this->prophesize(Route::class)->reveal();

        $this->routes
            ->route(
                '/foo',
                $preparedMiddleware,
                ['GET', 'POST'],
                null
            )
            ->willReturn($route);

        $this->assertSame($route, $this->app->route('/foo', $middleware, ['GET', 'POST']));
    }

    /**
     * @dataProvider validMiddleware
     */
    public function testRouteAcceptsPathMiddlewareMethodsAndName($middleware)
    {
        $preparedMiddleware = $this->createMockMiddleware();

        $this->middleware
            ->prepare($middleware)
            ->willReturn($preparedMiddleware);

        $route = $this->prophesize(Route::class)->reveal();

        $this->routes
            ->route(
                '/foo',
                $preparedMiddleware,
                ['GET', 'POST'],
                'foo'
            )
            ->willReturn($route);

        $this->assertSame($route, $this->app->route('/foo', $middleware, ['GET', 'POST'], 'foo'));
    }

    public function requestMethodsWithValidMiddleware() : iterable
    {
        foreach (['get', 'post', 'put', 'patch', 'delete'] as $method) {
            foreach ($this->validMiddleware() as $key => $data) {
                array_unshift($data, $method);
                $name = sprintf('%s-%s', $method, $key);
                yield $name => $data;
            }
        }
    }

    /**
     * @dataProvider requestMethodsWithValidMiddleware
     */
    public function testSpecificRouteMethodsCanAcceptOnlyPathAndMiddleware(string $method, $middleware)
    {
        $preparedMiddleware = $this->createMockMiddleware();

        $this->middleware
            ->prepare($middleware)
            ->willReturn($preparedMiddleware);

        $route = $this->prophesize(Route::class)->reveal();

        $this->routes
            ->route(
                '/foo',
                $preparedMiddleware,
                [strtoupper($method)],
                null
            )
            ->willReturn($route);

        $this->assertSame($route, $this->app->{$method}('/foo', $middleware));
    }

    /**
     * @dataProvider requestMethodsWithValidMiddleware
     */
    public function testSpecificRouteMethodsCanAcceptPathMiddlewareAndName(string $method, $middleware)
    {
        $preparedMiddleware = $this->createMockMiddleware();

        $this->middleware
            ->prepare($middleware)
            ->willReturn($preparedMiddleware);

        $route = $this->prophesize(Route::class)->reveal();

        $this->routes
            ->route(
                '/foo',
                $preparedMiddleware,
                [strtoupper($method)],
                'foo'
            )
            ->willReturn($route);

        $this->assertSame($route, $this->app->{$method}('/foo', $middleware, 'foo'));
    }

    /**
     * @dataProvider validMiddleware
     */
    public function testAnyMethodPassesNullForMethodWhenNoNamePresent($middleware)
    {
        $preparedMiddleware = $this->createMockMiddleware();

        $this->middleware
            ->prepare($middleware)
            ->willReturn($preparedMiddleware);

        $route = $this->prophesize(Route::class)->reveal();

        $this->routes
            ->route(
                '/foo',
                $preparedMiddleware,
                null,
                null
            )
            ->willReturn($route);

        $this->assertSame($route, $this->app->any('/foo', $middleware));
    }

    /**
     * @dataProvider validMiddleware
     */
    public function testAnyMethodPassesNullForMethodWhenAllArgumentsPresent($middleware)
    {
        $preparedMiddleware = $this->createMockMiddleware();

        $this->middleware
            ->prepare($middleware)
            ->willReturn($preparedMiddleware);

        $route = $this->prophesize(Route::class)->reveal();

        $this->routes
            ->route(
                '/foo',
                $preparedMiddleware,
                null,
                'foo'
            )
            ->willReturn($route);

        $this->assertSame($route, $this->app->any('/foo', $middleware, 'foo'));
    }

    public function testGetRoutesProxiesToRouteCollector()
    {
        $route = $this->prophesize(Route::class)->reveal();
        $this->routes->getRoutes()->willReturn([$route]);

        $this->assertSame([$route], $this->app->getRoutes());
    }
}
