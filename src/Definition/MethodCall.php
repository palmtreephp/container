<?php

namespace Palmtree\Container\Definition;

class MethodCall
{
    /** @var string */
    protected $name;
    /** @var array */
    protected $arguments = [];

    /**
     * @param array $args
     * @return MethodCall
     */
    public static function fromArray(array $args)
    {
        $methodCall = new static();

        $methodCall->setName($args['method']);

        if (isset($args['arguments'])) {
            $methodCall->setArguments($args['arguments']);
        }

        return $methodCall;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MethodCall
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return MethodCall
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }
}
