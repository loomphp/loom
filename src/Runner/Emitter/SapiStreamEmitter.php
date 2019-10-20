<?php

declare(strict_types=1);

namespace Loom\Runner\Emitter;

use Psr\Http\Message\ResponseInterface;

use function preg_match;
use function strlen;
use function substr;

class SapiStreamEmitter implements EmitterInterface
{
    use SapiEmitterTrait;

    private $maxBufferLength;

    public function __construct(int $maxBufferLength = 8192)
    {
        $this->maxBufferLength = $maxBufferLength;
    }

    public function emit(ResponseInterface $response) : bool
    {
        $this->assertNoPreviousOutput();
        $this->emitHeaders($response);
        $this->emitStatusLine($response);

        $range = $this->parseContentRange($response->getHeaderLine('Content-Range'));

        if (null === $range || 'bytes' !== $range[0]) {
            $this->emitBody($response);
            return true;
        }

        $this->emitBodyRange($range, $response);
        return true;
    }

    private function emitBody(ResponseInterface $response) : void
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        if (! $body->isReadable()) {
            echo $body;
            return;
        }

        while (! $body->eof()) {
            echo $body->read($this->maxBufferLength);
        }
    }

    private function emitBodyRange(array $range, ResponseInterface $response) : void
    {
        list($unit, $first, $last, $length) = $range;

        $body = $response->getBody();

        $length = $last - $first + 1;

        if ($body->isSeekable()) {
            $body->seek($first);

            $first = 0;
        }

        if (! $body->isReadable()) {
            echo substr($body->getContents(), $first, $length);
            return;
        }

        $remaining = $length;

        while ($remaining >= $this->maxBufferLength && ! $body->eof()) {
            $contents   = $body->read($this->maxBufferLength);
            $remaining -= strlen($contents);

            echo $contents;
        }

        if ($remaining > 0 && ! $body->eof()) {
            echo $body->read($remaining);
        }
    }

    private function parseContentRange(string $header) : ?array
    {
        if (! preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches)) {
            return null;
        }

        return [
            $matches['unit'],
            (int) $matches['first'],
            (int) $matches['last'],
            $matches['length'] === '*' ? '*' : (int) $matches['length'],
        ];
    }
}
