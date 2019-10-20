<?php

declare(strict_types=1);

namespace Loom\Response\Factory;

use Loom\Response\ErrorResponseGenerator;
use Loom\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class ErrorResponseGeneratorFactory
{
    public function __invoke(ContainerInterface $container) : ErrorResponseGenerator
    {
        $config = $container->has('config') ? $container->get('config') : [];

        $debug = $config['debug'] ?? false;
        $templated = $config['templated'] ?? true;

        $template = $config['loom']['error_handler']['template_error']
            ?? ErrorResponseGenerator::TEMPLATE_DEFAULT;

        $layout   = $config['loom']['error_handler']['layout']
            ?? ErrorResponseGenerator::LAYOUT_DEFAULT;

        $renderer = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        return new ErrorResponseGenerator($debug, $templated, $renderer, $template, $layout);
    }
}
