<?php

declare(strict_types=1);

namespace Loom\Helper;

use InvalidArgumentException;
use Loom\Router\RouteResult;
use Loom\Router\RouterInterface;

use function array_merge;
use function count;
use function http_build_query;
use function ltrim;
use function preg_match;
use function sprintf;

class UrlHelper
{
    /**
     * Regular expression used to validate fragment identifiers.
     *
     * @see RFC 3986: https://tools.ietf.org/html/rfc3986#section-3.5
     */
    const FRAGMENT_IDENTIFIER_REGEX = '/^([!$&\'()*+,;=._~:@\/?-]|%[0-9a-fA-F]{2}|[a-zA-Z0-9])+$/';

    /**
     * @var string
     */
    private $basePath = '/';

    /**
     * @var RouteResult
     */
    private $result;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function __invoke(
        string $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        string $fragmentIdentifier = null,
        array $options = []
    ) : string {
        $result = $this->getRouteResult();
        if ($routeName === null && $result === null) {
            throw new Exception\RuntimeException(
                'Attempting to use matched result when none was injected; aborting'
            );
        }

        $basePath = $this->getBasePath();
        if ($basePath === '/') {
            $basePath = '';
        }

        $routerOptions = $options['router'] ?? [];

        if ($routeName === null) {
            $path = $basePath . $this->generateUriFromResult($routeParams, $result, $routerOptions);
            $path = $this->appendQueryStringArguments($path, $queryParams);
            $path = $this->appendFragment($path, $fragmentIdentifier);
            return $path;
        }

        $reuseResultParams = ! isset($options['reuse_result_params']) || (bool) $options['reuse_result_params'];

        if ($result && $reuseResultParams) {
            $routeParams = $this->mergeParams($routeName, $result, $routeParams);
        }

        $path = $basePath . $this->router->generateUri($routeName, $routeParams, $routerOptions);

        $path = $this->appendQueryStringArguments($path, $queryParams);
        $path = $this->appendFragment($path, $fragmentIdentifier);

        return $path;
    }

    public function generate(
        string $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        string $fragmentIdentifier = null,
        array $options = []
    ) : string {
        return $this($routeName, $routeParams, $queryParams, $fragmentIdentifier, $options);
    }

    public function setRouteResult(RouteResult $result) : void
    {
        $this->result = $result;
    }

    public function setBasePath(string $path) : void
    {
        $this->basePath = '/' . ltrim($path, '/');
    }

    public function getRouteResult() : ?RouteResult
    {
        return $this->result;
    }

    public function getBasePath() : string
    {
        return $this->basePath;
    }

    private function generateUriFromResult(array $params, RouteResult $result, array $routerOptions) : string
    {
        if ($result->isFailure()) {
            throw new Exception\RuntimeException(
                'Attempting to use matched result when routing failed; aborting'
            );
        }

        $name   = $result->getMatchedRouteName();
        $params = array_merge($result->getMatchedParams(), $params);
        return $this->router->generateUri($name, $params, $routerOptions);
    }

    private function mergeParams(string $route, RouteResult $result, array $params) : array
    {
        if ($result->isFailure()) {
            return $params;
        }

        if ($result->getMatchedRouteName() !== $route) {
            return $params;
        }

        return array_merge($result->getMatchedParams(), $params);
    }

    private function appendQueryStringArguments(string $uriString, array $queryParams) : string
    {
        if (count($queryParams) > 0) {
            return sprintf('%s?%s', $uriString, http_build_query($queryParams));
        }
        return $uriString;
    }

    private function appendFragment(string $uriString, ?string $fragmentIdentifier) : string
    {
        if ($fragmentIdentifier !== null) {
            if (! preg_match(self::FRAGMENT_IDENTIFIER_REGEX, $fragmentIdentifier)) {
                throw new InvalidArgumentException('Fragment identifier must conform to RFC 3986', 400);
            }

            return sprintf('%s#%s', $uriString, $fragmentIdentifier);
        }
        return $uriString;
    }
}
