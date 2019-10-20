<?php

declare(strict_types=1);

namespace LoomTest\Factory;

use Loom\ApplicationStack;
use Loom\Factory\ApplicationFactory;
use Loom\Middleware\ApplicationMiddleware;
use Loom\Routing\RouteCollector;
use Loom\Runner\Runner;
use Loom\Seam\SeamMiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Loom\Application;

class ApplicationFactoryTest extends TestCase
{
    public function testFactoryProducesAnApplication()
    {
        $stack = $this->prophesize(ApplicationStack::class)->reveal();
        $seam = $this->prophesize(SeamMiddlewareInterface::class)->reveal();
        $routeCollector = $this->prophesize(RouteCollector::class)->reveal();
        $runner = $this->prophesize(Runner::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(ApplicationStack::class)->willReturn($stack);
        $container->get(ApplicationMiddleware::class)->willReturn($seam);
        $container->get(RouteCollector::class)->willReturn($routeCollector);
        $container->get(Runner::class)->willReturn($runner);

        $factory = new ApplicationFactory();

        $application = $factory($container->reveal());

        $this->assertInstanceOf(Application::class, $application);
        $this->assertAttributeSame($stack, 'stack', $application);
        $this->assertAttributeSame($seam, 'seam', $application);
        $this->assertAttributeSame($routeCollector, 'routes', $application);
        $this->assertAttributeSame($runner, 'runner', $application);
    }
}
