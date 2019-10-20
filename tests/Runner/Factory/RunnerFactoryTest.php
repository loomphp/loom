<?php

declare(strict_types=1);

namespace LoomTest\Runner\Factory;

use Loom\Middleware\ApplicationMiddleware;
use Loom\Response\ServerRequestErrorResponseGenerator;
use Loom\Runner\Emitter\EmitterInterface;
use Loom\Runner\Factory\RunnerFactory;
use Loom\Runner\Runner;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionProperty;
use RuntimeException;
use Throwable;

class RequestHandlerRunnerFactoryTest extends TestCase
{
    public function testFactoryProducesRunnerUsingServicesFromContainer()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $handler = $this->registerHandlerInContainer($container);
        $emitter = $this->registerEmitterInContainer($container);
        $serverRequestFactory = $this->registerServerRequestFactoryInContainer($container);
        $errorGenerator = $this->registerServerRequestErrorResponseGeneratorInContainer($container);

        $factory = new RunnerFactory();

        $runner = $factory($container->reveal());

        $this->assertInstanceOf(Runner::class, $runner);
        $this->assertAttributeSame($handler, 'handler', $runner);
        $this->assertAttributeSame($emitter, 'emitter', $runner);

        $this->assertAttributeNotSame($serverRequestFactory, 'serverRequestFactory', $runner);
        $this->assertAttributeNotSame($errorGenerator, 'serverRequestErrorResponseGenerator', $runner);

        $r = new ReflectionProperty($runner, 'serverRequestFactory');
        $r->setAccessible(true);
        $toTest = $r->getValue($runner);
        $this->assertSame($serverRequestFactory(), $toTest());

        $r = new ReflectionProperty($runner, 'serverRequestErrorResponseGenerator');
        $r->setAccessible(true);
        $toTest = $r->getValue($runner);
        $e = new RuntimeException();
        $this->assertSame($errorGenerator($e), $toTest($e));
    }

    public function registerHandlerInContainer($container) : RequestHandlerInterface
    {
        $app = $this->prophesize(RequestHandlerInterface::class)->reveal();
        $container->get(ApplicationMiddleware::class)->willReturn($app);
        return $app;
    }

    public function registerEmitterInContainer($container) : EmitterInterface
    {
        $emitter = $this->prophesize(EmitterInterface::class)->reveal();
        $container->get(EmitterInterface::class)->willReturn($emitter);
        return $emitter;
    }

    public function registerServerRequestFactoryInContainer($container) : callable
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $factory = function () use ($request) {
            return $request;
        };
        $container->get(ServerRequestInterface::class)->willReturn($factory);
        return $factory;
    }

    public function registerServerRequestErrorResponseGeneratorInContainer($container) : callable
    {
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $generator = $this->prophesize(ServerRequestErrorResponseGenerator::class);
        $generator->__invoke(Argument::type(Throwable::class))->willReturn($response);
        $container->get(ServerRequestErrorResponseGenerator::class)->willReturn($generator->reveal());
        return $generator->reveal();
    }
}
