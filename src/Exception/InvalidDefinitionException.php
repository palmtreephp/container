<?php

namespace Palmtree\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class InvalidDefinitionException extends \Exception implements ContainerExceptionInterface
{
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
