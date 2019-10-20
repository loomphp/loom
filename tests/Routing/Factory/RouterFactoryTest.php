<?php

declare(strict_types=1);

namespace LoomTest\Routing;

use Loom\Router\Router;
use Loom\Routing\Factory\RouterFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RouterFactoryTest extends TestCase
{
    /** @var RouterFactory */
    private $factory;

    private $container;

    protected function setUp()
    {
        $this->factory = new RouterFactory();
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testCreatesRouterWithEmptyConfig()
    {
        $this->container->has('config')->willReturn(false);

        $router = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(Router::class, $router);
        $this->assertAttributeSame(false, 'cacheEnabled', $router);
        $this->assertAttributeSame('data/cache/fastroute.php.cache', 'cacheFile', $router);
    }

    public function testCreatesRouterWithConfig()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'router' => [
                'fastroute' => [
                    Router::CONFIG_CACHE_ENABLED => true,
                    Router::CONFIG_CACHE_FILE => '/foo/bar/file-cache',
                ],
            ],
        ]);

        $router = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(Router::class, $router);
        $this->assertAttributeSame(true, 'cacheEnabled', $router);
        $this->assertAttributeSame('/foo/bar/file-cache', 'cacheFile', $router);
    }
}
