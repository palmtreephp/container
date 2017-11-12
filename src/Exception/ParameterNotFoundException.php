<?php

namespace Palmtree\Container\Exception;

class ParameterNotFoundException extends NotFoundException
{
    public function __construct($key, $code = 0, \Throwable $previous = null)
    {
        $this->message = sprintf("Parameter '%s' does not exist.", $key);
    }
}
