<?php

declare(strict_types=1);

namespace Loom\Middleware\Factory;

use Loom\Helper\UrlHelper;
use Loom\Middleware\Exception\MissingHelperException;
use Loom\Middleware\UrlHelperMiddleware;
use Psr\Container\ContainerInterface;

use function sprintf;

class UrlHelperMiddlewareFactory
{
    /** @var string */
    private $urlHelperServiceName;

    public static function __set_state(array $data) : self
    {
        return new self(
            $data['urlHelperServiceName'] ?? UrlHelper::class
        );
    }

    public function __construct(string $urlHelperServiceName = UrlHelper::class)
    {
        $this->urlHelperServiceName = $urlHelperServiceName;
    }

    public function __invoke(ContainerInterface $container) : UrlHelperMiddleware
    {
        if (! $container->has($this->urlHelperServiceName)) {
            throw new MissingHelperException(sprintf(
                '%s requires a %s service at instantiation; none found',
                UrlHelperMiddleware::class,
                $this->urlHelperServiceName
            ));
        }

        return new UrlHelperMiddleware($container->get($this->urlHelperServiceName));
    }
}
