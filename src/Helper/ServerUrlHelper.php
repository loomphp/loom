<?php

declare(strict_types=1);

namespace Loom\Helper;

use Psr\Http\Message\UriInterface;

use function preg_match;
use function rtrim;

class ServerUrlHelper
{
    /**
     * @var UriInterface
     */
    private $uri;

    public function __invoke(string $path = null) : string
    {
        $path = $path === null ? '' : $path;

        if ($this->uri instanceof UriInterface) {
            return $this->createUrlFromUri($path);
        }

        if (empty($path)) {
            return '/';
        }

        if ('/' === $path[0]) {
            return $path;
        }

        return '/' . $path;
    }

    public function generate(string $path = null) : string
    {
        return $this($path);
    }

    public function setUri(UriInterface $uri) : void
    {
        $this->uri = $uri;
    }

    private function createUrlFromUri(string $specification) : string
    {
        preg_match(
            '%^(?P<path>[^?#]*)(?:(?:\?(?P<query>[^#]*))?(?:\#(?P<fragment>.*))?)$%',
            (string) $specification,
            $matches
        );
        $path     = $matches['path'];
        $query    = isset($matches['query']) ? $matches['query'] : '';
        $fragment = isset($matches['fragment']) ? $matches['fragment'] : '';

        $uri = $this->uri
            ->withQuery('')
            ->withFragment('');

        // Relative path
        if (! empty($path) && '/' !== $path[0]) {
            $path = rtrim($this->uri->getPath(), '/') . '/' . $path;
        }

        if (! empty($path)) {
            $uri = $uri->withPath($path);
        }

        if (! empty($query)) {
            $uri = $uri->withQuery($query);
        }

        if (! empty($fragment)) {
            $uri = $uri->withFragment($fragment);
        }

        return (string) $uri;
    }
}
