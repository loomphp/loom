<?php

declare(strict_types=1);

namespace Loom\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Loom\Template\TemplateRendererInterface;

use function sprintf;

class NotFoundHandler implements RequestHandlerInterface
{
    public const TEMPLATE_DEFAULT = 'error::404';
    public const LAYOUT_DEFAULT = 'layout::default';

    /**
     * @var TemplateRendererInterface|null
     */
    private $renderer;

    /**
     * @var bool
     */
    private $templated;

    /**
     * @var callable
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $layout;

    public function __construct(
        callable $responseFactory,
        bool $isTemplatedMode = true,
        TemplateRendererInterface $renderer = null,
        string $template = self::TEMPLATE_DEFAULT,
        string $layout = self::LAYOUT_DEFAULT
    ) {
        // Factory cast to closure in order to provide return type safety.
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
        $this->templated = $isTemplatedMode;
        $this->renderer = $renderer;
        $this->template = $template;
        $this->layout = $layout;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if ($this->templated && $this->renderer) {
            return $this->generateTemplatedResponse($this->renderer, $request);
        }
        return $this->generatePlainTextResponse($request);
    }

    private function generatePlainTextResponse(ServerRequestInterface $request) : ResponseInterface
    {
        $response = ($this->responseFactory)()->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        $response->getBody()
            ->write(sprintf(
                'Cannot %s %s',
                $request->getMethod(),
                (string) $request->getUri()
            ));

        return $response;
    }

    private function generateTemplatedResponse(
        TemplateRendererInterface $renderer,
        ServerRequestInterface $request
    ) : ResponseInterface {

        $response = ($this->responseFactory)()->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        $response->getBody()->write(
            $renderer->render($this->template, ['request' => $request, 'layout' => $this->layout])
        );

        return $response;
    }
}
