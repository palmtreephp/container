<?php

namespace Palmtree\Container\Tests\Fixtures;

class Bar
{
    public static $instances = 0;

    public function __construct()
    {
        static::$instances++;
    }
}
