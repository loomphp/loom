<?php

declare(strict_types=1);

namespace Loom\Response\Factory;

use Loom\Response\ServerRequestErrorResponseGenerator;
use Loom\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ServerRequestErrorResponseGeneratorFactory
{
    public function __invoke(ContainerInterface $container) : ServerRequestErrorResponseGenerator
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $debug  = $config['debug'] ?? false;
        $templated = $config['templated'] ?? true;

        $renderer = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        $template = $config['zend-expressive']['error_handler']['template_error']
            ?? ServerRequestErrorResponseGenerator::TEMPLATE_DEFAULT;

        return new ServerRequestErrorResponseGenerator(
            $container->get(ResponseInterface::class),
            $debug,
            $templated,
            $renderer,
            $template
        );
    }
}
