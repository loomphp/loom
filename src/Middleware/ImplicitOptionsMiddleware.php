<?php

declare(strict_types=1);

namespace Loom\Middleware;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Loom\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function implode;

class ImplicitOptionsMiddleware implements MiddlewareInterface
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
        if ($request->getMethod() !== RequestMethod::METHOD_OPTIONS) {
            return $handler->handle($request);
        }

        $result = $request->getAttribute(RouteResult::class);
        if (! $result) {
            return $handler->handle($request);
        }

        if ($result->isFailure() && ! $result->isMethodFailure()) {
            return $handler->handle($request);
        }

        if ($result->getMatchedRoute()) {
            return $handler->handle($request);
        }

        $allowedMethods = $result->getAllowedMethods();

        return ($this->responseFactory)()->withHeader('Allow', implode(',', $allowedMethods));
    }
}
