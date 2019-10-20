<?php

declare(strict_types=1);

namespace Loom;

use Loom\Factory\ApplicationContainerFactory;
use Loom\Factory\ApplicationFactory;
use Loom\Factory\ApplicationStackFactory;
use Loom\Factory\ResponseFactory;
use Loom\Factory\ServerRequestFactoryFactory;
use Loom\Factory\StreamFactory;
use Loom\Handler\Factory\NotFoundHandlerFactory;
use Loom\Handler\NotFoundHandler;
use Loom\Helper\Factory\UrlHelperFactory;
use Loom\Helper\ServerUrlHelper;
use Loom\Helper\UrlHelper;
use Loom\Middleware\ApplicationMiddleware;
use Loom\Middleware\DispatchMiddleware;
use Loom\Middleware\ErrorHandlerMiddleware;
use Loom\Middleware\Factory\ApplicationMiddlewareFactory;
use Loom\Middleware\Factory\DispatchMiddlewareFactory;
use Loom\Middleware\Factory\ErrorHandlerMiddlewareFactory;
use Loom\Middleware\Factory\ImplicitHeadMiddlewareFactory;
use Loom\Middleware\Factory\ImplicitOptionsMiddlewareFactory;
use Loom\Middleware\Factory\MethodNotAllowedMiddlewareFactory;
use Loom\Middleware\Factory\RouteMiddlewareFactory;
use Loom\Middleware\Factory\ServerUrlMiddlewareFactory;
use Loom\Middleware\Factory\UrlHelperMiddlewareFactory;
use Loom\Middleware\ImplicitHeadMiddleware;
use Loom\Middleware\ImplicitOptionsMiddleware;
use Loom\Middleware\MethodNotAllowedMiddleware;
use Loom\Middleware\RouteMiddleware;
use Loom\Middleware\ServerUrlMiddleware;
use Loom\Middleware\UrlHelperMiddleware;
use Loom\Plates\Factory\RendererFactory;
use Loom\Response\ErrorResponseGenerator;
use Loom\Response\Factory\ErrorResponseGeneratorFactory;
use Loom\Response\Factory\ServerRequestErrorResponseGeneratorFactory;
use Loom\Response\ServerRequestErrorResponseGenerator;
use Loom\Router\Router;
use Loom\Router\RouterInterface;
use Loom\Routing\Factory\RouteCollectorFactory;
use Loom\Routing\Factory\RouterFactory;
use Loom\Routing\RouteCollector;
use Loom\Runner\Emitter\Factory\EmitterFactory;
use Loom\Runner\Emitter\EmitterInterface;
use Loom\Runner\Runner;
use Loom\Runner\Factory\RunnerFactory;
use Loom\Template\PlatesRenderer;
use Loom\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [
                // helpers
                ServerUrlHelper::class => ServerUrlHelper::class,
            ],
            'aliases' => [
                // routing
                RouterInterface::class => Router::class,
                // plates
                TemplateRendererInterface::class => PlatesRenderer::class,
            ],
            'factories' => [
                // application
                Application::class => ApplicationFactory::class,
                ApplicationContainer::class => ApplicationContainerFactory::class,
                ApplicationStack::class => ApplicationStackFactory::class,
                // handler
                NotFoundHandler::class => NotFoundHandlerFactory::class,
                // routing
                Router::class => RouterFactory::class,
                RouteCollector::class => RouteCollectorFactory::class,
                // helpers
                UrlHelper::class => UrlHelperFactory::class,
                // plates
                PlatesRenderer::class => RendererFactory::class,
                // request
                ServerRequestInterface::class => ServerRequestFactoryFactory::class,
                // response
                ErrorResponseGenerator::class => ErrorResponseGeneratorFactory::class,
                ResponseInterface::class => ResponseFactory::class,
                ServerRequestErrorResponseGenerator::class => ServerRequestErrorResponseGeneratorFactory::class,
                // runner
                EmitterInterface::class => EmitterFactory::class,
                Runner::class => RunnerFactory::class,
                // stream
                StreamInterface::class => StreamFactory::class,
                /**
                 * middleware
                 */
                // application
                ApplicationMiddleware::class => ApplicationMiddlewareFactory::class,
                // response
                ErrorHandlerMiddleware::class => ErrorHandlerMiddlewareFactory::class,
                // routing
                DispatchMiddleware::class => DispatchMiddlewareFactory::class,
                ImplicitHeadMiddleware::class => ImplicitHeadMiddlewareFactory::class,
                ImplicitOptionsMiddleware::class => ImplicitOptionsMiddlewareFactory::class,
                MethodNotAllowedMiddleware::class => MethodNotAllowedMiddlewareFactory::class,
                RouteMiddleware::class => RouteMiddlewareFactory::class,
                // helpers
                ServerUrlMiddleware::class => ServerUrlMiddlewareFactory::class,
                UrlHelperMiddleware::class => UrlHelperMiddlewareFactory::class,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'extension' => 'phtml',
        ];
    }
}
