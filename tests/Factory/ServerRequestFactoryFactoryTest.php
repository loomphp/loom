<?php

declare(strict_types=1);

namespace LoomTest\Container;

use Closure;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Loom\Factory\ServerRequestFactoryFactory;

class ServerRequestFactoryFactoryTest extends TestCase
{
    public function testFactoryProducesACallableCapableOfGeneratingAServerRequest()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new ServerRequestFactoryFactory();

        $result = $factory($container);

        $this->assertInternalType('callable', $result);

        $response = $result();
        $this->assertInstanceOf(ServerRequest::class, $response);
    }
}
