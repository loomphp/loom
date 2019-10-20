<?php

declare(strict_types=1);

namespace Loom\Runner;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class Runner
{
    /**
     * @var Emitter\EmitterInterface
     */
    private $emitter;

    /**
     *
     * @var RequestHandlerInterface
     */
    private $handler;

    /**
     * @var callable
     */
    private $serverRequestErrorResponseGenerator;

    /**
     * @var callable
     */
    private $serverRequestFactory;

    public function __construct(
        RequestHandlerInterface $handler,
        Emitter\EmitterInterface $emitter,
        callable $serverRequestFactory,
        callable $serverRequestErrorResponseGenerator
    ) {
        $this->handler = $handler;
        $this->emitter = $emitter;

        // Factories are cast as Closures to ensure return type safety.
        $this->serverRequestFactory = function () use ($serverRequestFactory) : ServerRequestInterface {
            return $serverRequestFactory();
        };

        $this->serverRequestErrorResponseGenerator =
            function (Throwable $exception) use ($serverRequestErrorResponseGenerator) : ResponseInterface {
                return $serverRequestErrorResponseGenerator($exception);
            };
    }

    public function run() : void
    {
        try {
            $request = ($this->serverRequestFactory)();
        } catch (Throwable $e) {
            $this->emitMarshalServerRequestException($e);
            return;
        }

        $response = $this->handler->handle($request);

        $this->emitter->emit($response);
    }

    private function emitMarshalServerRequestException(Throwable $exception) : void
    {
        $response = ($this->serverRequestErrorResponseGenerator)($exception);
        $this->emitter->emit($response);
    }
}
