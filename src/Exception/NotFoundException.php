<?php

namespace Palmtree\ServiceContainer\Exception;

class NotFoundException extends \Exception
{
    public function __construct($thing, $code = 0, \Throwable $previous = null)
    {
        $this->message = sprintf("'%s' was not found.", $thing);
    }
}
