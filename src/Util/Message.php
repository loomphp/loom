<?php

declare(strict_types=1);

namespace Loom\Util;

use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class Message
{
    public static function getStatusCode(Throwable $error, ResponseInterface $response): int
    {
        $errorCode = $error->getCode();
        if ($errorCode >= 400 && $errorCode < 600) {
            return $errorCode;
        }

        $status = $response->getStatusCode();
        if (! $status || $status < 400 || $status >= 600) {
            $status = 500;
        }
        return $status;
    }
}
