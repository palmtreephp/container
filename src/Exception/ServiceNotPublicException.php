<?php

namespace Palmtree\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class ServiceNotPublicException extends \Exception implements ContainerExceptionInterface
{
    public function __construct($key)
    {
        $this->message = "Service '$key' is not public and can only be used via dependency injection";
    }
}
