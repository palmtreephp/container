<?php

namespace Palmtree\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ParameterNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public function __construct($key, $code = 0, \Throwable $previous = null)
    {
        $this->message = "Parameter '$key' does not exist.";
    }
}
