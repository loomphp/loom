<?php

declare(strict_types=1);

namespace LoomTest\Runner\Emitter\Factory;

use Loom\Runner\Emitter\EmitterStack;
use Loom\Runner\Emitter\Factory\EmitterFactory;
use Loom\Runner\Emitter\SapiEmitter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function array_shift;
use function iterator_to_array;

class EmitterFactoryTest extends TestCase
{
    public function testFactoryProducesEmitterStackWithSapiEmitterComposed()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new EmitterFactory();

        $emitter = $factory($container);

        $this->assertInstanceOf(EmitterStack::class, $emitter);

        $emitters = iterator_to_array($emitter);
        $this->assertCount(1, $emitters);

        $emitter = array_shift($emitters);
        $this->assertInstanceOf(SapiEmitter::class, $emitter);
    }
}
