<?php

declare(strict_types=1);

namespace Loom\Middleware;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Loom\Router\RouteResult;
use Loom\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ImplicitHeadMiddleware implements MiddlewareInterface
{
    public const FORWARDED_HTTP_METHOD_ATTRIBUTE = 'forwarded_http_method';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var callable
     */
    private $streamFactory;

    public function __construct(RouterInterface $router, callable $streamFactory)
    {
        $this->router = $router;

        // Factory is wrapped in closur in order to enforce return type safety.
        $this->streamFactory = function () use ($streamFactory) : StreamInterface {
            return $streamFactory();
        };
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() !== RequestMethod::METHOD_HEAD) {
            return $handler->handle($request);
        }

        $result = $request->getAttribute(RouteResult::class);
        if (! $result) {
            return $handler->handle($request);
        }

        if ($result->getMatchedRoute()) {
            return $handler->handle($request);
        }

        $routeResult = $this->router->match($request->withMethod(RequestMethod::METHOD_GET));
        if ($routeResult->isFailure()) {
            return $handler->handle($request);
        }

        foreach ($routeResult->getMatchedParams() as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }

        $response = $handler->handle(
            $request
                ->withAttribute(RouteResult::class, $routeResult)
                ->withMethod(RequestMethod::METHOD_GET)
                ->withAttribute(self::FORWARDED_HTTP_METHOD_ATTRIBUTE, RequestMethod::METHOD_HEAD)
        );

        $body = ($this->streamFactory)();
        return $response->withBody($body);
    }
}
