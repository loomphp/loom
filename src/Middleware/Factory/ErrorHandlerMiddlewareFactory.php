<?php

declare(strict_types=1);

namespace Loom\Middleware\Factory;

use Loom\Middleware\ErrorHandlerMiddleware;
use Loom\Response\ErrorResponseGenerator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ErrorHandlerMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : ErrorHandlerMiddleware
    {
        $generator = $container->has(ErrorResponseGenerator::class)
            ? $container->get(ErrorResponseGenerator::class)
            : null;

        return new ErrorHandlerMiddleware($container->get(ResponseInterface::class), $generator);
    }
}
