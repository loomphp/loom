<?php

declare(strict_types=1);

namespace Loom\Runner\Emitter;

use Psr\Http\Message\ResponseInterface;

interface EmitterInterface
{
    /**
     * Emit a response.
     *
     * @param ResponseInterface $response
     * @return bool
     */
    public function emit(ResponseInterface $response) : bool;
}
