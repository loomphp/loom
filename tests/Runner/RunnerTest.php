<?php

declare(strict_types=1);

namespace LoomTest\Runner;

use Exception;
use Loom\Runner\Emitter\EmitterInterface;
use Loom\Runner\Runner;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use TypeError;

class RunnerTest extends TestCase
{
    public function testUsesErrorResponseGeneratorToGenerateResponseWhenRequestFactoryRaisesException()
    {
        $exception = new Exception();
        $serverRequestFactory = function () use ($exception) {
            throw $exception;
        };

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $errorResponseGenerator = function ($e) use ($exception, $response) {
            Assert::assertSame($exception, $e);
            return $response;
        };

        $emitter = $this->prophesize(EmitterInterface::class);
        $emitter->emit($response)->shouldBeCalled();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::any())->shouldNotBeCalled();

        $runner = new Runner(
            $handler->reveal(),
            $emitter->reveal(),
            $serverRequestFactory,
            $errorResponseGenerator
        );

        $this->assertNull($runner->run());
    }

    public function testRunPassesRequestGeneratedByRequestFactoryToHandleWhenNoRequestPassedToRun()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();

        $serverRequestFactory = function () use ($request) {
            return $request;
        };

        $errorResponseGenerator = function ($e) {
            Assert::fail('Should never hit error response generator');
        };

        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $emitter = $this->prophesize(EmitterInterface::class);
        $emitter->emit($response)->shouldBeCalled();

        $runner = new Runner(
            $handler->reveal(),
            $emitter->reveal(),
            $serverRequestFactory,
            $errorResponseGenerator
        );

        $this->assertNull($runner->run());
    }

    public function testRaisesTypeErrorIfServerRequestFactoryDoesNotReturnARequestInstance()
    {
        $serverRequestFactory = function () {
            return null;
        };

        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $errorResponseGenerator = function (Throwable $e) use ($response) {
            Assert::assertInstanceOf(TypeError::class, $e);
            return $response;
        };

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::any())->shouldNotBeCalled();

        $emitter = $this->prophesize(EmitterInterface::class);
        $emitter->emit($response)->shouldBeCalled();

        $runner = new Runner(
            $handler->reveal(),
            $emitter->reveal(),
            $serverRequestFactory,
            $errorResponseGenerator
        );

        $this->assertNull($runner->run());
    }

    public function testRaisesTypeErrorIfServerErrorResponseGeneratorFactoryDoesNotReturnAResponse()
    {
        $serverRequestFactory = function () {
            return null;
        };

        $errorResponseGenerator = function (Throwable $e) {
            Assert::assertInstanceOf(TypeError::class, $e);
            return null;
        };

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle(Argument::any())->shouldNotBeCalled();

        $emitter = $this->prophesize(EmitterInterface::class);
        $emitter->emit(Argument::any())->shouldNotBeCalled();

        $runner = new Runner(
            $handler->reveal(),
            $emitter->reveal(),
            $serverRequestFactory,
            $errorResponseGenerator
        );

        $this->expectException(TypeError::class);
        $runner->run();
    }
}
