<?php

declare(strict_types=1);

namespace LoomTest\Plates\TestAsset;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class TestExtension implements ExtensionInterface
{
    public static $engine;

    public function register(Engine $engine)
    {
        self::$engine = $engine;
    }
}
