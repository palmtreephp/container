<?php

namespace Palmtree\Container\Tests\Fixtures\Service;

class PhpDefinedService
{
    private $args;
    private $string;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param mixed $string
     */
    public function setString($string)
    {
        $this->string = $string;
    }

    /**
     * @return mixed
     */
    public function getString()
    {
        return $this->string;
    }
}
