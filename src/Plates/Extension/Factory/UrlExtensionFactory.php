<?php

declare(strict_types=1);

namespace Loom\Plates\Extension\Factory;

use Loom\Plates\Extension\UrlExtension;
use Psr\Container\ContainerInterface;
use Loom\Helper\ServerUrlHelper;
use Loom\Helper\UrlHelper;
use Loom\Plates\Exception\MissingHelperException;

use function sprintf;

class UrlExtensionFactory
{
    public function __invoke(ContainerInterface $container) : UrlExtension
    {
        if (! $container->has(UrlHelper::class)) {
            throw new MissingHelperException(sprintf(
                '%s requires that the %s service be present; not found',
                UrlExtension::class,
                UrlHelper::class
            ));
        }

        if (! $container->has(ServerUrlHelper::class)) {
            throw new MissingHelperException(sprintf(
                '%s requires that the %s service be present; not found',
                UrlExtension::class,
                ServerUrlHelper::class
            ));
        }

        return new UrlExtension(
            $container->get(UrlHelper::class),
            $container->get(ServerUrlHelper::class)
        );
    }
}
