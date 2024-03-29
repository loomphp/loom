<?php

declare(strict_types=1);

namespace Loom\Handler\Factory;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Loom\Handler\NotFoundHandler;
use Loom\Template\TemplateRendererInterface;

class NotFoundHandlerFactory
{
    public function __invoke(ContainerInterface $container) : NotFoundHandler
    {
        $config   = $container->has('config') ? $container->get('config') : [];
        $templated = $config['templated'] ?? true;
        $renderer = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;
        $template = $config['loom']['error_handler']['template_404']
            ?? NotFoundHandler::TEMPLATE_DEFAULT;
        $layout   = $config['loom']['error_handler']['layout']
            ?? NotFoundHandler::LAYOUT_DEFAULT;

        return new NotFoundHandler(
            $container->get(ResponseInterface::class),
            $templated,
            $renderer,
            $template,
            $layout
        );
    }
}
