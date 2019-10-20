<?php

declare(strict_types=1);

namespace Loom\Factory;

use Loom\ApplicationContainer;
use Psr\Container\ContainerInterface;

class ApplicationContainerFactory
{
    public function __invoke(ContainerInterface $container) : ApplicationContainer
    {
        return new ApplicationContainer($container);
    }
}
