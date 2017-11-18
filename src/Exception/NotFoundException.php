<?php

namespace Palmtree\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    public function __construct($thing, $code = 0, \Throwable $previous = null)
    {
        $this->message = sprintf("'%s' was not found.", $thing);
    }
}
