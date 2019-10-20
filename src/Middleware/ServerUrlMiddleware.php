<?php

declare(strict_types=1);

namespace Loom\Middleware;

use Loom\Helper\ServerUrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ServerUrlMiddleware implements MiddlewareInterface
{
    /**
     * @var ServerUrlHelper
     */
    private $helper;

    public function __construct(ServerUrlHelper $helper)
    {
        $this->helper = $helper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->helper->setUri($request->getUri());
        return $handler->handle($request);
    }
}
