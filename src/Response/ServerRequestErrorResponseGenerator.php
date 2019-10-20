<?php

declare(strict_types=1);

namespace Loom\Response;

use Loom\Template\TemplateRendererInterface;
use Loom\Util\Message;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ServerRequestErrorResponseGenerator
{
    use ErrorResponseGeneratorTrait;

    public const TEMPLATE_DEFAULT = 'error::error';

    /**
     * @var callable
     */
    private $responseFactory;

    public function __construct(
        callable $responseFactory,
        bool $isDevelopmentMode = false,
        bool $isTemplatedMode = true,
        TemplateRendererInterface $renderer = null,
        string $template = self::TEMPLATE_DEFAULT
    ) {
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };

        $this->debug     = $isDevelopmentMode;
        $this->templated = $isTemplatedMode;
        $this->renderer  = $renderer;
        $this->template  = $template;
    }

    public function __invoke(Throwable $e) : ResponseInterface
    {
        $response = ($this->responseFactory)();
        $response = $response->withStatus(Message::getStatusCode($e, $response));

        if ($this->templated && $this->renderer) {
            return $this->prepareTemplatedResponse(
                $e,
                $this->renderer,
                [
                    'response' => $response,
                    'status'   => $response->getStatusCode(),
                    'reason'   => $response->getReasonPhrase(),
                ],
                $this->debug,
                $response
            );
        }

        return $this->prepareDefaultResponse($e, $this->debug, $response);
    }
}
