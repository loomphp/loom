<?php

declare(strict_types=1);

namespace ZendTest\Expressive\Container;

use Loom\ApplicationContainer;
use Loom\ApplicationStack;
use Loom\Factory\ApplicationStackFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ApplicationStackFactoryTest extends TestCase
{
    public function testFactoryProducesApplicationStackComposingApplicationContainerInstance()
    {
        $applicationContainer = $this->prophesize(ApplicationContainer::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(ApplicationContainer::class)->willReturn($applicationContainer);

        $stack = new ApplicationStackFactory();

        $applicationStack = $stack($container->reveal());

        $this->assertInstanceOf(ApplicationStack::class, $applicationStack);
        $this->assertAttributeSame($applicationContainer, 'container', $applicationStack);
    }
}
