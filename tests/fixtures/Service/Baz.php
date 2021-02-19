<?php

namespace Palmtree\Container\Tests\Fixtures\Service;

class Baz
{
    /** @var Foo */
    private $foo;

    /**
     * Baz constructor.
     */
    public function __construct(Foo $foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return Foo
     */
    public function getFoo()
    {
        return $this->foo;
    }
}
