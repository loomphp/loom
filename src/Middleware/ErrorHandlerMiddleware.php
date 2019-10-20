<?php

declare(strict_types=1);

namespace Loom\Middleware;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function error_reporting;
use function in_array;
use function restore_error_handler;
use function set_error_handler;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @var callable[]
     */
    private $listeners = [];

    /**
     * @var callable
     */
    private $responseGenerator;

    /**
     * @var callable
     */
    private $responseFactory;

    public function __construct(callable $responseFactory, callable $responseGenerator)
    {
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
        $this->responseGenerator = $responseGenerator;
    }

    public function attachListener(callable $listener) : void
    {
        if (in_array($listener, $this->listeners, true)) {
            return;
        }

        $this->listeners[] = $listener;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        set_error_handler($this->createErrorHandler());

        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
            $response = $this->handleThrowable($e, $request);
        }

        restore_error_handler();

        return $response;
    }

    private function handleThrowable(Throwable $e, ServerRequestInterface $request) : ResponseInterface
    {
        $generator = $this->responseGenerator;
        $response = $generator($e, $request, ($this->responseFactory)());
        $this->triggerListeners($e, $request, $response);
        return $response;
    }

    private function createErrorHandler() : callable
    {

        return function (int $errno, string $errstr, string $errfile, int $errline) : void {
            if (! (error_reporting() & $errno)) {
                return;
            }

            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        };
    }

    private function triggerListeners(
        Throwable $error,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : void {
        foreach ($this->listeners as $listener) {
            $listener($error, $request, $response);
        }
    }
}
