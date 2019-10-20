<?php

declare(strict_types=1);

namespace Loom\Runner\Emitter;

use Psr\Http\Message\ResponseInterface;

class SapiEmitter implements EmitterInterface
{
    use SapiEmitterTrait;

    public function emit(ResponseInterface $response) : bool
    {
        $this->assertNoPreviousOutput();

        $this->emitHeaders($response);
        $this->emitStatusLine($response);
        $this->emitBody($response);

        return true;
    }

    private function emitBody(ResponseInterface $response) : void
    {
        echo $response->getBody();
    }
}
