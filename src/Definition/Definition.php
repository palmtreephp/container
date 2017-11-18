<?php

namespace Palmtree\Container\Definition;

use Palmtree\Container\Exception\InvalidDefinitionException;

class Definition
{
    /** @var string */
    protected $class;
    /** @var bool */
    protected $lazy = false;
    /** @var array */
    protected $arguments = [];
    /** @var MethodCall[] */
    protected $methodCalls = [];

    /**
     * @param array $args
     *
     * @return Definition
     *
     * @throws InvalidDefinitionException
     */
    public static function fromArray(array $args)
    {
        if (!isset($args['class'])) {
            throw new InvalidDefinitionException("Missing required 'class' argument. Must be a FQCN.");
        }

        $definition = new static();

        $definition->setClass($args['class']);
        $definition->setLazy(isset($args['lazy']) ? $args['lazy'] : false);

        if (isset($args['arguments'])) {
            $definition->setArguments($args['arguments']);
        }

        if (isset($args['calls'])) {
            foreach ($args['calls'] as $call) {
                $methodCall = MethodCall::fromArray($call);
                $definition->addMethodCall($methodCall);
            }
        }

        return $definition;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return Definition
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLazy()
    {
        return $this->lazy;
    }

    /**
     * @param bool $lazy
     * @return Definition
     */
    public function setLazy($lazy)
    {
        $this->lazy = (bool)$lazy;
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
     *
     * @return Definition
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @param int $index
     * @param mixed $newArg
     * @return Definition
     * @throws \InvalidArgumentException
     */
    public function replaceArgument($index, $newArg)
    {
        if (!array_key_exists($index, $this->arguments)) {
            throw new \InvalidArgumentException("Key $index does not exist.");
        }

        $this->arguments[$index] = $newArg;

        return $this;
    }

    /**
     * @param MethodCall $methodCall
     *
     * @return Definition
     */
    public function addMethodCall(MethodCall $methodCall)
    {
        $this->methodCalls[] = $methodCall;

        return $this;
    }

    /**
     * @return MethodCall[]
     */
    public function getMethodCalls()
    {
        return $this->methodCalls;
    }

    /**
     * @param MethodCall[] $methodCalls
     * @return Definition
     */
    public function setMethodCalls(array $methodCalls)
    {
        foreach ($methodCalls as $methodCall) {
            $this->addMethodCall($methodCall);
        }

        return $this;
    }
}
