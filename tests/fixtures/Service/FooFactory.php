<?php

namespace Palmtree\Container\Tests\Fixtures\Service;

class FooFactory
{
    /**
     * @return Foo
     */
    public static function createFoo(Bar $bar)
    {
        $foo = new Foo($bar, 1, true);

        return $foo;
    }
}
