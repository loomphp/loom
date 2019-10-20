<?php

declare(strict_types=1);

namespace Loom\Factory;

use Loom\ApplicationContainer;
use Loom\ApplicationStack;
use Psr\Container\ContainerInterface;

class ApplicationStackFactory
{
    public function __invoke(ContainerInterface $container) : ApplicationStack
    {
        return new ApplicationStack(
            $container->get(ApplicationContainer::class)
        );
    }
}
