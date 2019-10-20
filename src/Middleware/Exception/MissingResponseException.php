<?php

declare(strict_types=1);

namespace Loom\Middleware\Exception;

use OutOfBoundsException;
use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class MissingResponseException extends OutOfBoundsException implements ExceptionInterface
{
    public static function forCallableMiddleware(callable $middleware): self
    {
        $type = is_object($middleware)
            ? get_class($middleware)
            : gettype($middleware);

        return new self(sprintf(
            'Decorated callable middleware of type %s failed to produce a response.',
            $type
        ));
    }
}
