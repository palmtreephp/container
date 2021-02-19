<?php

namespace Palmtree\Container\Tests\Fixtures\Service;

class LazyLoad
{
    public static $instances = 0;

    public function __construct()
    {
        ++static::$instances;
    }
}
