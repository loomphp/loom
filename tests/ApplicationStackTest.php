<?php

declare(strict_types=1);

namespace ZendTest\Expressive;

use Loom\Middleware\CallableMiddleware;
use Loom\Seam\SeamMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionProperty;
use Loom\Exception;
use Loom\Middleware\LazyLoadingMiddleware;
use Loom\ApplicationContainer;
use Loom\ApplicationStack;
use Loom\Middleware\DispatchMiddleware;
use Loom\Middleware\RequestHandlerMiddleware;

use function array_shift;
use function iterator_to_array;

class ApplicationStackTest extends TestCase
{
    /**
     * @var ApplicationContainer
     */
    private $container;

    /**
     * @var ApplicationStack
     */
    private $stack;

    public function setUp()
    {
        $this->container = $this->prophesize(ApplicationContainer::class);
        $this->stack = new ApplicationStack($this->container->reveal());
    }

    public function assertCallableMiddleware(callable $expectedCallable, MiddlewareInterface $middleware)
    {
        $this->assertInstanceOf(CallableMiddleware::class, $middleware);
        $this->assertAttributeSame($expectedCallable, 'middleware', $middleware);
    }

    public function assertLazyLoadingMiddleware(string $expectedMiddlewareName, MiddlewareInterface $middleware)
    {
        $this->assertInstanceOf(LazyLoadingMiddleware::class, $middleware);
        $this->assertAttributeSame($this->container->reveal(), 'container', $middleware);
        $this->assertAttributeSame($expectedMiddlewareName, 'middlewareName', $middleware);
    }

    public function assertSeam(array $expectedSeam, MiddlewareInterface $middleware)
    {
        $this->assertInstanceOf(SeamMiddleware::class, $middleware);
        $pipeline = $this->reflectSeam($middleware);
        $this->assertSame($expectedSeam, $pipeline);
    }

    /**
     * @param SeamMiddleware $seamMiddleware
     * @return array
     * @throws \ReflectionException
     */
    public function reflectSeam(SeamMiddleware $seamMiddleware) : array
    {
        $r = new ReflectionProperty($seamMiddleware, 'queue');
        $r->setAccessible(true);
        return iterator_to_array($r->getValue($seamMiddleware));
    }

    public function testCallableDecoratesCallableMiddleware()
    {
        $callable = function ($request, $handler) {
        };

        $middleware = $this->stack->callable($callable);
        $this->assertCallableMiddleware($callable, $middleware);
    }

    public function testLazyLoadingMiddlewareDecoratesMiddlewareServiceName()
    {
        $middleware = $this->stack->lazy('service');
        $this->assertLazyLoadingMiddleware('service', $middleware);
    }

    public function testPrepareReturnsMiddlewareImplementationsVerbatim()
    {
        $middleware = $this->prophesize(MiddlewareInterface::class)->reveal();
        $this->assertSame($middleware, $this->stack->prepare($middleware));
    }

    public function testPrepareDecoratesCallables()
    {
        $callable = function ($request, $handler) {
        };

        $middleware = $this->stack->prepare($callable);
        $this->assertInstanceOf(CallableMiddleware::class, $middleware);
        $this->assertAttributeSame($callable, 'middleware', $middleware);
    }

    public function testPrepareDecoratesServiceNamesAsLazyLoadingMiddleware()
    {
        $middleware = $this->stack->prepare('service');
        $this->assertInstanceOf(LazyLoadingMiddleware::class, $middleware);
        $this->assertAttributeSame('service', 'middlewareName', $middleware);
        $this->assertAttributeSame($this->container->reveal(), 'container', $middleware);
    }

    public function testPrepareDecoratesArraysAsMiddlewarePipes()
    {
        $middleware1 = $this->prophesize(MiddlewareInterface::class)->reveal();
        $middleware2 = $this->prophesize(MiddlewareInterface::class)->reveal();
        $middleware3 = $this->prophesize(MiddlewareInterface::class)->reveal();

        $middleware = $this->stack->prepare([$middleware1, $middleware2, $middleware3]);
        $this->assertSeam([$middleware1, $middleware2, $middleware3], $middleware);
    }

    public function invalidMiddlewareTypes() : iterable
    {
        yield 'null' => [null];
        yield 'false' => [false];
        yield 'true' => [true];
        yield 'zero' => [0];
        yield 'int' => [1];
        yield 'zero-float' => [0.0];
        yield 'float' => [1.1];
        yield 'object' => [(object) ['foo' => 'bar']];
    }

    /**
     * @dataProvider invalidMiddlewareTypes
     */
    public function testPrepareRaisesExceptionForTypesItDoesNotUnderstand($middleware)
    {
        $this->expectException(Exception\InvalidMiddlewareException::class);
        $this->stack->prepare($middleware);
    }

    public function testPipelineAcceptsMultipleArguments()
    {
        $middleware1 = $this->prophesize(MiddlewareInterface::class)->reveal();
        $middleware2 = $this->prophesize(MiddlewareInterface::class)->reveal();
        $middleware3 = $this->prophesize(MiddlewareInterface::class)->reveal();

        $middleware = $this->stack->stitching($middleware1, $middleware2, $middleware3);
        $this->assertSeam([$middleware1, $middleware2, $middleware3], $middleware);
    }

    public function testPipelineAcceptsASingleArrayArgument()
    {
        $middleware1 = $this->prophesize(MiddlewareInterface::class)->reveal();
        $middleware2 = $this->prophesize(MiddlewareInterface::class)->reveal();
        $middleware3 = $this->prophesize(MiddlewareInterface::class)->reveal();

        $middleware = $this->stack->stitching([$middleware1, $middleware2, $middleware3]);
        $this->assertSeam([$middleware1, $middleware2, $middleware3], $middleware);
    }

    public function validPrepareTypes()
    {
        yield 'service' => ['service', 'assertLazyLoadingMiddleware', 'service'];

        $callable = function ($request, $handler) {
        };
        yield 'callable' => [$callable, 'assertCallableMiddleware', $callable];

        $middleware = new DispatchMiddleware();
        yield 'instance' => [$middleware, 'assertSame', $middleware];
    }

    /**
     * @dataProvider validPrepareTypes
     */
    public function testPipelineAllowsAnyTypeSupportedByPrepare(
        $middleware,
        string $assertion,
        $expected
    ) {
        $queue = $this->stack->stitching($middleware);
        $this->assertInstanceOf(SeamMiddleware::class, $queue);

        $r = new ReflectionProperty($queue, 'queue');
        $r->setAccessible(true);
        $values = iterator_to_array($r->getValue($queue));
        $received = array_shift($values);

        $this->{$assertion}($expected, $received);
    }

    public function testSeamAllowsStitchingArraysOfMiddlewareAndCastsThemToInternalQueue()
    {
        $callable = function ($request, $handler) {
        };
        $middleware = new DispatchMiddleware();

        $internalStitch = [$callable, $middleware];

        $queue = $this->stack->stitching($internalStitch);

        $this->assertInstanceOf(SeamMiddleware::class, $queue);
        $received = $this->reflectSeam($queue);
        $this->assertCount(2, $received);
        $this->assertCallableMiddleware($callable, $received[0]);
        $this->assertSame($middleware, $received[1]);
    }

    public function testPrepareDecoratesRequestHandlersAsMiddleware()
    {
        $handler = $this->prophesize(RequestHandlerInterface::class)->reveal();
        $middleware = $this->stack->prepare($handler);
        $this->assertInstanceOf(RequestHandlerMiddleware::class, $middleware);
        $this->assertAttributeSame($handler, 'handler', $middleware);
    }

    public function testHandlerDecoratesRequestHandlersAsMiddleware()
    {
        $handler = $this->prophesize(RequestHandlerInterface::class)->reveal();
        $middleware = $this->stack->handler($handler);
        $this->assertInstanceOf(RequestHandlerMiddleware::class, $middleware);
        $this->assertAttributeSame($handler, 'handler', $middleware);
    }
}
