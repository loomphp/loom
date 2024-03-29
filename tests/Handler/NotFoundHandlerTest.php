<?php

declare(strict_types=1);

namespace LoomTest\Handler;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Loom\Handler\NotFoundHandler;
use Loom\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundHandlerTest extends TestCase
{
    /** @var ServerRequestInterface|ObjectProphecy */
    private $request;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    /** @var callable */
    private $responseFactory;

    public function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->responseFactory = function () {
            return $this->response->reveal();
        };
    }

    public function testImplementsRequestHandler()
    {
        $handler = new NotFoundHandler($this->responseFactory);
        $this->assertInstanceOf(RequestHandlerInterface::class, $handler);
    }

    public function testConstructorDoesNotRequireARenderer()
    {
        $handler = new NotFoundHandler($this->responseFactory);
        $this->assertInstanceOf(NotFoundHandler::class, $handler);
    }

    public function testConstructorCanAcceptRendererAndTemplate()
    {
        $renderer = $this->prophesize(TemplateRendererInterface::class)->reveal();
        $template = 'foo::bar';
        $layout = 'layout::error';

        $handler = new NotFoundHandler($this->responseFactory, true, $renderer, $template, $layout);

        $this->assertInstanceOf(NotFoundHandler::class, $handler);
        $this->assertAttributeSame($renderer, 'renderer', $handler);
        $this->assertAttributeEquals($template, 'template', $handler);
        $this->assertAttributeEquals($layout, 'layout', $handler);
    }

    public function testRendersDefault404ResponseWhenNoRendererPresent()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn(RequestMethod::METHOD_POST);
        $request->getUri()->willReturn('https://example.com/foo/bar');

        $stream = $this->prophesize(StreamInterface::class);
        $stream->write('Cannot POST https://example.com/foo/bar')->shouldBeCalled();
        $this->response->withStatus(StatusCode::STATUS_NOT_FOUND)->will([$this->response, 'reveal']);
        $this->response->getBody()->will([$stream, 'reveal']);

        $handler = new NotFoundHandler($this->responseFactory);

        $response = $handler->handle($request->reveal());

        $this->assertSame($this->response->reveal(), $response);
    }

    public function testUsesRendererToGenerateResponseContentsWhenPresent()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();

        $renderer = $this->prophesize(TemplateRendererInterface::class);
        $renderer
            ->render(
                NotFoundHandler::TEMPLATE_DEFAULT,
                [
                    'request' => $request,
                    'layout' => NotFoundHandler::LAYOUT_DEFAULT,
                ]
            )
            ->willReturn('CONTENT');

        $stream = $this->prophesize(StreamInterface::class);
        $stream->write('CONTENT')->shouldBeCalled();

        $this->response->withStatus(StatusCode::STATUS_NOT_FOUND)->will([$this->response, 'reveal']);
        $this->response->getBody()->will([$stream, 'reveal']);

        $handler = new NotFoundHandler($this->responseFactory, true, $renderer->reveal());

        $response = $handler->handle($request);

        $this->assertSame($this->response->reveal(), $response);
    }
}
