<?php

declare(strict_types=1);

namespace LoomTest\Plates\Factory;

use League\Plates\Engine as PlatesEngine;
use League\Plates\Extension\ExtensionInterface;
use LoomTest\Plates\TestAsset\TestExtension;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;
use Psr\Container\ContainerInterface;
use stdClass;
use Loom\Helper\ServerUrlHelper;
use Loom\Helper\UrlHelper;
use Loom\Plates\Exception\InvalidExtensionException;
use Loom\Plates\Extension\UrlExtension;
use Loom\Plates\Factory\EngineFactory;

use function is_string;

class EngineFactoryTest extends TestCase
{
    /** @var ContainerInterface|ProphecyInterface */
    private $container;

    public function setUp()
    {
        TestExtension::$engine = null;
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->has(UrlHelper::class)->willReturn(true);
        $this->container->get(UrlHelper::class)->willReturn(
            $this->prophesize(UrlHelper::class)->reveal()
        );

        $this->container->has(ServerUrlHelper::class)->willReturn(true);
        $this->container->get(ServerUrlHelper::class)->willReturn(
            $this->prophesize(ServerUrlHelper::class)->reveal()
        );

        $this->container->has(UrlExtension::class)->willReturn(false);
    }

    public function testFactoryReturnsPlatesEngine()
    {
        $this->container->has('config')->willReturn(false);
        $factory = new EngineFactory();
        $engine = $factory($this->container->reveal());
        $this->assertInstanceOf(PlatesEngine::class, $engine);
        return $engine;
    }

    /**
     * @depends testFactoryReturnsPlatesEngine
     *
     * @param PlatesEngine $engine
     */
    public function testUrlExtensionIsRegisteredByDefault(PlatesEngine $engine)
    {
        $this->assertTrue($engine->doesFunctionExist('url'));
        $this->assertTrue($engine->doesFunctionExist('serverurl'));
    }


    public function testFactoryCanRegisterConfiguredExtensions()
    {
        $extensionOne = $this->prophesize(ExtensionInterface::class);
        $extensionOne->register(Argument::type(PlatesEngine::class))->shouldBeCalled();

        $extensionTwo = $this->prophesize(ExtensionInterface::class);
        $extensionTwo->register(Argument::type(PlatesEngine::class))->shouldBeCalled();
        $this->container->has('ExtensionTwo')->willReturn(true);
        $this->container->get('ExtensionTwo')->willReturn($extensionTwo->reveal());

        $this->container->has(TestExtension::class)->willReturn(false);

        $config = [
            'plates' => [
                'extensions' => [
                    $extensionOne->reveal(),
                    'ExtensionTwo',
                    TestExtension::class,
                ],
            ],
        ];
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $factory = new EngineFactory();
        $engine = $factory($this->container->reveal());
        $this->assertInstanceOf(PlatesEngine::class, $engine);

        // Test that the TestExtension was registered. The other two extensions
        // are verified via mocking.
        $this->assertSame($engine, TestExtension::$engine);
    }

    public function invalidExtensions()
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'zero' => [0],
            'int' => [1],
            'zero-float' => [0.0],
            'float' => [1.1],
            'non-class-string' => ['not-a-class'],
            'array' => [['not-an-extension']],
            'non-extension-object' => [(object) ['extension' => 'not-really']],
        ];
    }

    /**
     * @dataProvider invalidExtensions
     *
     * @param mixed $extension
     */
    public function testFactoryRaisesExceptionForInvalidExtensions($extension)
    {
        $config = [
            'plates' => [
                'extensions' => [
                    $extension,
                ],
            ],
        ];
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        if (is_string($extension)) {
            $this->container->has($extension)->willReturn(false);
        }

        $factory = new EngineFactory();
        $this->expectException(InvalidExtensionException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenAttemptingToInjectAnInvalidExtensionService()
    {
        $config = [
            'plates' => [
                'extensions' => [
                    'FooExtension',
                ],
            ],
        ];
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $this->container->has('FooExtension')->willReturn(true);
        $this->container->get('FooExtension')->willReturn(new stdClass());

        $factory = new EngineFactory();
        $this->expectException(InvalidExtensionException::class);
        $this->expectExceptionMessage('ExtensionInterface');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenNonServiceClassIsAnInvalidExtension()
    {
        $config = [
            'plates' => [
                'extensions' => [
                    stdClass::class,
                ],
            ],
        ];
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $this->container->has(stdClass::class)->willReturn(false);

        $factory = new EngineFactory();
        $this->expectException(InvalidExtensionException::class);
        $this->expectExceptionMessage('ExtensionInterface');
        $factory($this->container->reveal());
    }

    public function provideHelpersToUnregister()
    {
        return [
            'url-only' => [[UrlHelper::class]],
            'server-url-only' => [[ServerUrlHelper::class]],
            'both' => [[ServerUrlHelper::class, UrlHelper::class]],
        ];
    }

    /**
     * @dataProvider provideHelpersToUnregister
     *
     * @param array $helpers
     */
    public function testUrlExtensionIsNotLoadedIfHelpersAreNotRegistered(array $helpers)
    {
        $this->container->has('config')->willReturn(false);
        foreach ($helpers as $helper) {
            $this->container->has($helper)->willReturn(false);
        }

        $factory = new EngineFactory();
        $engine = $factory($this->container->reveal());

        $this->assertFalse($engine->doesFunctionExist('url'));
        $this->assertFalse($engine->doesFunctionExist('serverurl'));
    }
}
