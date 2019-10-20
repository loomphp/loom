<?php

declare(strict_types=1);

namespace Loom\Plates\Factory;

use League\Plates\Engine as PlatesEngine;
use League\Plates\Extension\ExtensionInterface;
use Loom\Plates\Exception\InvalidExtensionException;
use Loom\Plates\Extension\Factory\UrlExtensionFactory;
use Loom\Plates\Extension\UrlExtension;
use Psr\Container\ContainerInterface;
use Loom\Helper;

use function class_exists;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;

class EngineFactory
{
    public function __invoke(ContainerInterface $container) : PlatesEngine
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['plates'] ?? [];

        $engine = new PlatesEngine();

        $this->injectUrlExtension($container, $engine);

        if (isset($config['extensions']) && is_array($config['extensions'])) {
            $this->injectExtensions($container, $engine, $config['extensions']);
        }

        return $engine;
    }

    private function injectUrlExtension(ContainerInterface $container, PlatesEngine $engine) : void
    {
        if ($container->has(UrlExtension::class)) {
            $engine->loadExtension($container->get(UrlExtension::class));
            return;
        }

        if (! $container->has(Helper\UrlHelper::class) || ! $container->has(Helper\ServerUrlHelper::class)) {
            return;
        }

        $extensionFactory = new UrlExtensionFactory();
        $engine->loadExtension($extensionFactory($container));
    }

    private function injectExtensions(ContainerInterface $container, PlatesEngine $engine, array $extensions) : void
    {
        foreach ($extensions as $extension) {
            $this->injectExtension($container, $engine, $extension);
        }
    }

    private function injectExtension(ContainerInterface $container, PlatesEngine $engine, $extension) : void
    {
        if ($extension instanceof ExtensionInterface) {
            $engine->loadExtension($extension);
            return;
        }

        if (! is_string($extension)) {
            throw new InvalidExtensionException(sprintf(
                '%s expects extension instances, service names, or class names; received %s',
                __CLASS__,
                (is_object($extension) ? get_class($extension) : gettype($extension))
            ));
        }

        if (! $container->has($extension) && ! class_exists($extension)) {
            throw new InvalidExtensionException(sprintf(
                '%s expects extension service names or class names; "%s" does not resolve to either',
                __CLASS__,
                $extension
            ));
        }

        $extension = $container->has($extension)
            ? $container->get($extension)
            : new $extension();

        if (! $extension instanceof ExtensionInterface) {
            throw new InvalidExtensionException(sprintf(
                '%s expects extension services to implement %s ; received %s',
                __CLASS__,
                ExtensionInterface::class,
                (is_object($extension) ? get_class($extension) : gettype($extension))
            ));
        }

        $engine->loadExtension($extension);
    }
}
