<?php

declare(strict_types=1);

namespace LoomTest\Handler\Factory;

use Loom\Handler\Factory\NotFoundHandlerFactory;
use Loom\Handler\NotFoundHandler;
use Loom\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class NotFoundHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    protected function setUp()
    {
        $this->response = $this->prophesize(ResponseInterface::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ResponseInterface::class)->willReturn(function () {
            return $this->response;
        });
    }

    public function testFactoryCreatesInstanceWithoutRendererIfRendererServiceIsMissing()
    {
        $this->container->has('config')->willReturn(false);
        $this->container->has(TemplateRendererInterface::class)->willReturn(false);
        $factory = new NotFoundHandlerFactory();

        $handler = $factory($this->container->reveal());
        $this->assertInstanceOf(NotFoundHandler::class, $handler);
        $this->assertAttributeInternalType('callable', 'responseFactory', $handler);
        $this->assertAttributeEmpty('renderer', $handler);
    }

    public function testFactoryCreatesInstanceUsingRendererServiceWhenPresent()
    {
        $renderer = $this->prophesize(TemplateRendererInterface::class)->reveal();
        $this->container->has('config')->willReturn(false);
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container->get(TemplateRendererInterface::class)->willReturn($renderer);
        $factory = new NotFoundHandlerFactory();

        $handler = $factory($this->container->reveal());
        $this->assertAttributeSame($renderer, 'renderer', $handler);
    }

    public function testFactoryUsesConfigured404TemplateWhenPresent()
    {
        $config = [
            'loom' => [
                'error_handler' => [
                    'layout' => 'layout::error',
                    'template_404' => 'foo::bar',
                ],
            ],
        ];
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->has(TemplateRendererInterface::class)->willReturn(false);
        $factory = new NotFoundHandlerFactory();

        $handler = $factory($this->container->reveal());
        $this->assertAttributeEquals(
            $config['loom']['error_handler']['layout'],
            'layout',
            $handler
        );
        $this->assertAttributeEquals(
            $config['loom']['error_handler']['template_404'],
            'template',
            $handler
        );
    }
}
