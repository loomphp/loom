<?php

declare(strict_types=1);

namespace LoomTest\Factory;

use Loom\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Stream;

class StreamFactoryTest extends TestCase
{
    public function testFactoryProducesACallableCapableOfGeneratingAStream()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new StreamFactory();

        $result = $factory($container);

        $this->assertInternalType('callable', $result);

        $stream = $result();
        $this->assertInstanceOf(Stream::class, $stream);
    }
}
