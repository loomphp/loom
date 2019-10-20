<?php

declare(strict_types=1);

namespace Loom\Plates\Extension;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Loom\Helper\ServerUrlHelper;
use Loom\Helper\UrlHelper;
use Loom\Router\RouteResult;

class UrlExtension implements ExtensionInterface
{
    /**
     * @var ServerUrlHelper
     */
    private $serverUrlHelper;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    public function __construct(UrlHelper $urlHelper, ServerUrlHelper $serverUrlHelper)
    {
        $this->urlHelper = $urlHelper;
        $this->serverUrlHelper = $serverUrlHelper;
    }

    public function register(Engine $engine) : void
    {
        $engine->registerFunction('url', $this->urlHelper);
        $engine->registerFunction('serverurl', $this->serverUrlHelper);
        $engine->registerFunction('route', [$this->urlHelper, 'getRouteResult']);
    }

    public function getRouteResult() : ?RouteResult
    {
        return $this->urlHelper->getRouteResult();
    }

    public function generateUrl(
        string $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        ?string $fragmentIdentifier = null,
        array $options = []
    ) {
        return $this->urlHelper->generate($routeName, $routeParams, $queryParams, $fragmentIdentifier, $options);
    }

    public function generateServerUrl(string $path = null) : string
    {
        return $this->serverUrlHelper->generate($path);
    }
}
