<?php

declare(strict_types=1);

namespace Loom\Helper\Factory;

use Loom\Helper\Exception\MissingRouterException;
use Loom\Helper\UrlHelper;
use Psr\Container\ContainerInterface;
use Loom\Router\RouterInterface;

use function sprintf;

class UrlHelperFactory
{
    /** @var string Base path for the URL helper */
    private $basePath;

    /** @var string $routerServiceName */
    private $routerServiceName;

    public static function __set_state(array $data) : self
    {
        return new self(
            $data['basePath'] ?? '/',
            $data['routerServiceName'] ?? RouterInterface::class
        );
    }

    public function __construct(string $basePath = '/', string $routerServiceName = RouterInterface::class)
    {
        $this->basePath = $basePath;
        $this->routerServiceName = $routerServiceName;
    }

    public function __invoke(ContainerInterface $container) : UrlHelper
    {
        if (! $container->has($this->routerServiceName)) {
            throw new MissingRouterException(sprintf(
                '%s requires a %s implementation; none found in container',
                UrlHelper::class,
                $this->routerServiceName
            ));
        }

        $helper = new UrlHelper($container->get($this->routerServiceName));
        $helper->setBasePath($this->basePath);
        return $helper;
    }
}
