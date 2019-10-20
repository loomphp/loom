<?php

declare(strict_types=1);

namespace Loom\Exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

use function sprintf;

class MissingDependencyException extends RuntimeException implements ExceptionInterface, NotFoundExceptionInterface
{
    public static function forMiddlewareService(string $service) : self
    {
        return new self(sprintf(
            'Cannot fetch middleware service "%s"; service not registered,'
            . ' or does not resolve to an autoloadable class name',
            $service
        ));
    }
}
