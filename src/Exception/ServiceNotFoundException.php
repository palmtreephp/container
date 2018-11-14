<?php

namespace Palmtree\Container\Exception;

class ServiceNotFoundException extends NotFoundException
{
    public function __construct($key, $code = 0, \Throwable $previous = null)
    {
        $this->message = "Service '$key' does not exist.";
    }
}
