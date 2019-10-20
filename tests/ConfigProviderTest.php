<?php

declare(strict_types=1);

namespace LoomTest;

use Loom\ConfigProvider;
use Loom\Helper\ServerUrlHelper;
use Loom\Helper\UrlHelper;
use Loom\Middleware\DispatchMiddleware;
use Loom\Middleware\ImplicitHeadMiddleware;
use Loom\Middleware\ImplicitOptionsMiddleware;
use Loom\Middleware\MethodNotAllowedMiddleware;
use Loom\Middleware\RouteMiddleware;
use Loom\Middleware\ServerUrlMiddleware;
use Loom\Middleware\UrlHelperMiddleware;
use Loom\Router\Router;
use Loom\Router\RouterInterface;
use Loom\Routing\RouteCollector;
use Loom\Template\PlatesRenderer;
use Loom\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /**
     * @var ConfigProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray(): array
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);

        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config): void
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
        $this->assertArrayHasKey('templates', $config);

        /**
         * invokables
         */
        $this->assertArrayHasKey('invokables', $config['dependencies']);
        $this->assertInternalType('array', $config['dependencies']['invokables']);
        $this->assertArrayHasKey(ServerUrlHelper::class, $config['dependencies']['invokables']);

        /**
         * aliases
         */
        $this->assertArrayHasKey('aliases', $config['dependencies']);
        $this->assertInternalType('array', $config['dependencies']['aliases']);
        $this->assertArrayHasKey(RouterInterface::class, $config['dependencies']['aliases']);
        $this->assertArrayHasKey(TemplateRendererInterface::class, $config['dependencies']['aliases']);
        /**
         * factories
         */
        $this->assertArrayHasKey('factories', $config['dependencies']);
        $factories = $config['dependencies']['factories'];
        $this->assertInternalType('array', $factories);
        // routing
        $this->assertArrayHasKey(Router::class, $factories);
        $this->assertArrayHasKey(RouteCollector::class, $factories);
        // helpers
        $this->assertArrayHasKey(UrlHelper::class, $factories);
        // plates
        $this->assertArrayHasKey(PlatesRenderer::class, $factories);
        /**
         * middleware
         */
        // routing
        $this->assertArrayHasKey(DispatchMiddleware::class, $factories);
        $this->assertArrayHasKey(ImplicitHeadMiddleware::class, $factories);
        $this->assertArrayHasKey(ImplicitOptionsMiddleware::class, $factories);
        $this->assertArrayHasKey(MethodNotAllowedMiddleware::class, $factories);
        $this->assertArrayHasKey(RouteMiddleware::class, $factories);
        // helpers
        $this->assertArrayHasKey(ServerUrlMiddleware::class, $factories);
        $this->assertArrayHasKey(UrlHelperMiddleware::class, $factories);
    }
}
