<?php

namespace Palmtree\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public function __construct($key, $code = 0, \Throwable $previous = null)
    {
        $this->message = "Service '$key' does not exist.";
    }
}
