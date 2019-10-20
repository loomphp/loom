<?php

declare(strict_types=1);

namespace Loom\Runner\Emitter;

use Loom\Runner\Exception;
use Psr\Http\Message\ResponseInterface;
use SplStack;

class EmitterStack extends SplStack implements EmitterInterface
{
    public function emit(ResponseInterface $response): bool
    {
        foreach ($this as $emitter) {
            if (false !== $emitter->emit($response)) {
                return true;
            }
        }

        return false;
    }

    public function offsetSet($index, $emitter)
    {
        $this->validateEmitter($emitter);
        parent::offsetSet($index, $emitter);
    }

    public function push($emitter)
    {
        $this->validateEmitter($emitter);
        parent::push($emitter);
    }

    public function unshift($emitter)
    {
        $this->validateEmitter($emitter);
        parent::unshift($emitter);
    }

    private function validateEmitter($emitter): void
    {
        if (! $emitter instanceof EmitterInterface) {
            throw Exception\InvalidEmitterException::forEmitter($emitter);
        }
    }
}
