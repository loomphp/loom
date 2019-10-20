<?php

declare(strict_types=1);

namespace LoomTest\Response\Factory;

use Loom\Response\ErrorResponseGenerator;
use Loom\Response\Factory\ErrorResponseGeneratorFactory;
use Loom\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class ErrorResponseGeneratorFactoryTest extends TestCase
{
    /** @var ContainerInterface */
    private $container;

    /** @var TemplateRendererInterface */
    private $renderer;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->renderer  = $this->prophesize(TemplateRendererInterface::class);
    }

    public function testNoConfigurationCreatesInstanceWithDefaults()
    {
        $this->container->has('config')->willReturn(false);
        $this->container->has(TemplateRendererInterface::class)->willReturn(false);
        $factory = new ErrorResponseGeneratorFactory();

        $generator = $factory($this->container->reveal());

        $this->assertInstanceOf(ErrorResponseGenerator::class, $generator);
        $this->assertAttributeEquals(false, 'debug', $generator);
        $this->assertAttributeEquals(true, 'templated', $generator);
        $this->assertAttributeEmpty('renderer', $generator);
        $this->assertAttributeEquals('error::error', 'template', $generator);
        $this->assertAttributeEquals('layout::default', 'layout', $generator);
    }

    public function testUsesDebugConfigurationToSetDebugFlag()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['debug' => true]);
        $this->container->has(TemplateRendererInterface::class)->willReturn(false);
        $factory = new ErrorResponseGeneratorFactory();

        $generator = $factory($this->container->reveal());

        $this->assertAttributeEquals(true, 'debug', $generator);
        $this->assertAttributeEmpty('renderer', $generator);
        $this->assertAttributeEquals('error::error', 'template', $generator);
        $this->assertAttributeEquals('layout::default', 'layout', $generator);
    }

    public function testUsesConfiguredTemplateRenderToSetGeneratorRenderer()
    {
        $this->container->has('config')->willReturn(false);
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container->get(TemplateRendererInterface::class)->will([$this->renderer, 'reveal']);
        $factory = new ErrorResponseGeneratorFactory();

        $generator = $factory($this->container->reveal());

        $this->assertAttributeEquals(false, 'debug', $generator);
        $this->assertAttributeSame($this->renderer->reveal(), 'renderer', $generator);
        $this->assertAttributeEquals('error::error', 'template', $generator);
        $this->assertAttributeEquals('layout::default', 'layout', $generator);
    }

    public function testUsesTemplateConfigurationToSetTemplate()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'loom' => [
                'error_handler' => [
                    'template_error' => 'error::custom',
                    'layout' => 'layout::custom',
                ],
            ],
        ]);
        $this->container->has(TemplateRendererInterface::class)->willReturn(false);
        $factory = new ErrorResponseGeneratorFactory();

        $generator = $factory($this->container->reveal());

        $this->assertAttributeEquals(false, 'debug', $generator);
        $this->assertAttributeEmpty('renderer', $generator);
        $this->assertAttributeEquals('error::custom', 'template', $generator);
        $this->assertAttributeEquals('layout::custom', 'layout', $generator);
    }
}
