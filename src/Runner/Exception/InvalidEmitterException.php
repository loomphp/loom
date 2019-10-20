<?php

declare(strict_types=1);

namespace Loom\Runner\Exception;

use InvalidArgumentException;
use Loom\Runner\Emitter;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class InvalidEmitterException extends InvalidArgumentException implements ExceptionInterface
{
    public static function forEmitter($emitter) : self
    {
        return new self(sprintf(
            '%s can only compose %s implementations; received %s',
            Emitter\EmitterStack::class,
            Emitter\EmitterInterface::class,
            is_object($emitter) ? get_class($emitter) : gettype($emitter)
        ));
    }
}
