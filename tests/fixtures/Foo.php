<?php

namespace Palmtree\Container\Tests\Fixtures;

class Foo
{
    /** @var Bar */
    protected $bar;
    protected $baz;

    public function __construct(Bar $bar, $baz)
    {
        $this->bar = $bar;
        $this->baz = $baz;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function getBaz()
    {
        return $this->baz;
    }
}
