<?php

declare(strict_types=1);

namespace LoomTest\Plates\Extension;

use League\Plates\Engine;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ProphecyInterface;
use Loom\Helper\ServerUrlHelper;
use Loom\Helper\UrlHelper;
use Loom\Plates\Extension\UrlExtension;
use Loom\Router\RouteResult;

class UrlExtensionTest extends TestCase
{
    /** @var UrlHelper|ProphecyInterface */
    private $urlHelper;

    /** @var ServerUrlHelper|ProphecyInterface */
    private $serverUrlHelper;

    /** @var UrlExtension */
    private $extension;

    public function setUp()
    {
        $this->urlHelper       = $this->prophesize(UrlHelper::class);
        $this->serverUrlHelper = $this->prophesize(ServerUrlHelper::class);

        $this->extension = new UrlExtension(
            $this->urlHelper->reveal(),
            $this->serverUrlHelper->reveal()
        );
    }

    public function testRegistersUrlFunctionWithEngine()
    {
        $engine = $this->prophesize(Engine::class);
        $engine
            ->registerFunction('url', $this->urlHelper)
            ->shouldBeCalled();
        $engine
            ->registerFunction('serverurl', $this->serverUrlHelper)
            ->shouldBeCalled();
        $engine
            ->registerFunction('route', [$this->urlHelper, 'getRouteResult'])
            ->shouldBeCalled();

        $this->extension->register($engine->reveal());
    }

    public function urlHelperParams()
    {
        return [
            'null'             => [null, []],
            'route-only'       => ['route', []],
            'params-only'      => [null, ['param' => 'value']],
            'route-and-params' => ['route', ['param' => 'value']],
        ];
    }

    /**
     * @dataProvider urlHelperParams
     *
     * @param null|string $route
     * @param array $params
     */
    public function testGenerateUrlProxiesToUrlHelper($route, array $params)
    {
        $this->urlHelper->generate($route, $params, [], null, [])->willReturn('/success');
        $this->assertEquals('/success', $this->extension->generateUrl($route, $params));
    }

    public function testUrlHelperAcceptsQueryParametersFragmentAndOptions()
    {
        $this->urlHelper->generate(
            'resource',
            ['id' => 'sha1'],
            ['foo' => 'bar'],
            'fragment',
            ['reuse_result_params' => true]
        )->willReturn('PATH');

        $this->assertEquals(
            'PATH',
            $this->extension->generateUrl(
                'resource',
                ['id' => 'sha1'],
                ['foo' => 'bar'],
                'fragment',
                ['reuse_result_params' => true]
            )
        );
    }

    public function serverUrlHelperParams()
    {
        return [
            'null'          => [null],
            'absolute-path' => ['/foo/bar'],
            'relative-path' => ['foo/bar'],
        ];
    }

    /**
     * @dataProvider serverUrlHelperParams
     *
     * @param null|string $path
     */
    public function testGenerateServerUrlProxiesToServerUrlHelper($path)
    {
        $this->serverUrlHelper->generate($path)->willReturn('/success');
        $this->assertEquals('/success', $this->extension->generateServerUrl($path));
    }

    public function testGetRouteResultReturnsRouteResultWhenPopulated()
    {
        $result = $this->prophesize(RouteResult::class);
        $this->urlHelper->getRouteResult()->willReturn($result->reveal());

        $this->assertInstanceOf(RouteResult::class, $this->extension->getRouteResult());
    }

    public function testGetRouteResultReturnsNullWhenRouteResultNotPopulatedInUrlHelper()
    {
        $this->urlHelper->getRouteResult()->willReturn(null);

        $this->assertNull($this->extension->getRouteResult());
    }
}
