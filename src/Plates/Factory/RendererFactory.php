<?php

declare(strict_types=1);

namespace Loom\Plates\Factory;

use League\Plates\Engine as PlatesEngine;
use Loom\Template\PlatesRenderer;
use Psr\Container\ContainerInterface;

use function is_array;
use function is_numeric;

class RendererFactory
{
    public function __invoke(ContainerInterface $container) : PlatesRenderer
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['templates'] ?? [];

        $engine = $this->createEngine($container);

        if (isset($config['extension'])) {
            $engine->setFileExtension($config['extension']);
        }

        $plates = new PlatesRenderer($engine);

        $allPaths = isset($config['paths']) && is_array($config['paths']) ? $config['paths'] : [];
        foreach ($allPaths as $namespace => $paths) {
            $namespace = is_numeric($namespace) ? null : $namespace;
            foreach ((array) $paths as $path) {
                $plates->addPath($path, $namespace);
            }
        }

        return $plates;
    }

    private function createEngine(ContainerInterface $container) : PlatesEngine
    {
        if ($container->has(PlatesEngine::class)) {
            return $container->get(PlatesEngine::class);
        }

        $engineFactory = new EngineFactory();
        return $engineFactory($container);
    }
}
