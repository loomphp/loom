<?php

declare(strict_types=1);

namespace LoomTest\Factory;

use Loom\Factory\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response;

class ResponseFactoryTest extends TestCase
{
    public function testFactoryProducesACallableCapableOfGeneratingAResponse()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new ResponseFactory();

        $result = $factory($container);

        $this->assertInternalType('callable', $result);

        $response = $result();
        $this->assertInstanceOf(Response::class, $response);
    }
}
