<?php

namespace Palmtree\Container\Exception;

class ContainerBuiltException extends ContainerException
{
    public function __construct($id, $code = 0, \Throwable $previous = null)
    {
        $this->message = sprintf("Service '%s' does not exist.", $id);
    }
}
