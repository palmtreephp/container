<?php

namespace Palmtree\Container\Tests\Fixtures\Service;

class Foo
{
    private $bar;
    private $baz = false;

    public function __construct(Bar $bar, $number, $bool)
    {
        $this->bar = $bar;
    }

    /**
     * @return Bar
     */
    public function getBar()
    {
        return $this->bar;
    }

    /**
     * @param bool $baz
     */
    public function setBaz($baz)
    {
        $this->baz = $baz;
    }

    /**
     * @return bool
     */
    public function getBaz()
    {
        return $this->baz;
    }
}
