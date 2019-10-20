<?php

declare(strict_types=1);

namespace LoomTest\Factory;

use Loom\ApplicationContainer;
use Loom\Factory\ApplicationContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ApplicationContainerFactoryTest extends TestCase
{
    public function testFactoryCreatesMiddlewareContainerUsingProvidedContainer()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new ApplicationContainerFactory();

        $applicationContainer = $factory($container);

        $this->assertInstanceOf(ApplicationContainer::class, $applicationContainer);
        $this->assertAttributeSame($container, 'container', $applicationContainer);
    }
}
