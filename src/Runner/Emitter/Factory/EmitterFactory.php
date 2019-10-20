<?php

declare(strict_types=1);

namespace Loom\Runner\Emitter\Factory;

use Loom\Runner\Emitter\EmitterInterface;
use Loom\Runner\Emitter\EmitterStack;
use Loom\Runner\Emitter\SapiEmitter;
use Psr\Container\ContainerInterface;

class EmitterFactory
{
    public function __invoke(ContainerInterface $container) : EmitterInterface
    {
        $stack = new EmitterStack();
        $stack->push(new SapiEmitter());
        return $stack;
    }
}
