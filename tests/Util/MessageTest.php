<?php

declare(strict_types=1);

namespace LommTest\Util;

use Loom\Util\Message;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;

class MessageTest extends TestCase
{
    public function testGetStatusCode()
    {
        $expectedResponse = $this->prophesize(ResponseInterface::class)->reveal();
        $exception = new \RuntimeException('Exception raised', 503);
        $this->assertSame(Message::getStatusCode($exception, $expectedResponse), $exception->getCode());
        $exception = new \RuntimeException('Exception raised', 603);
        $this->assertSame(Message::getStatusCode($exception, $expectedResponse), 500);

    }
}
