<?php

declare(strict_types=1);

namespace Loom\Middleware;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Loom\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function implode;

class MethodNotAllowedMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    private $responseFactory;

    public function __construct(callable $responseFactory)
    {
        // Factories is wrapped in a closure in order to enforce return type safety.
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeResult = $request->getAttribute(RouteResult::class);
        if (! $routeResult || ! $routeResult->isMethodFailure()) {
            return $handler->handle($request);
        }

        return ($this->responseFactory)()
            ->withStatus(StatusCode::STATUS_METHOD_NOT_ALLOWED)
            ->withHeader('Allow', implode(',', $routeResult->getAllowedMethods()));
    }
}
