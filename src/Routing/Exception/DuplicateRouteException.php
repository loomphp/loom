<?php

declare(strict_types=1);

namespace Loom\Routing\Exception;

use DomainException;

class DuplicateRouteException extends DomainException implements ExceptionInterface
{
}
