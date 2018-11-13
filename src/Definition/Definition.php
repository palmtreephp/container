<?php

namespace Palmtree\Container\Definition;

use Palmtree\Container\Exception\InvalidDefinitionException;

class Definition
{
    /** @var string */
    private $class;
    /** @var bool */
    private $lazy = false;
    /** @var array */
    private $arguments = [];
    /** @var MethodCall[] */
    private $methodCalls = [];
    /** @var array */
    private $factory;

    /**
     * @param array $yaml
     *
     * @return Definition
     *
     * @throws InvalidDefinitionException
     */
    public static function fromYaml(array $yaml)
    {
        if (!isset($yaml['class']) && !isset($yaml['factory'])) {
            throw new InvalidDefinitionException("Missing required 'class' argument. Must be a FQCN.");
        }

        $definition = new self();

        if (isset($yaml['class'])) {
            $definition->setClass($yaml['class']);
        }

        if (isset($yaml['factory'])) {
            $definition->setFactory($yaml['factory']);
        }

        $definition->setLazy(isset($yaml['lazy']) ? $yaml['lazy'] : false);

        if (isset($yaml['arguments'])) {
            $definition->setArguments($yaml['arguments']);
        }

        if (isset($yaml['calls'])) {
            foreach ($yaml['calls'] as $call) {
                $methodCall = MethodCall::fromYaml($call);
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
     *
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
     *
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
     *
     * @return Definition
     */
    public function setMethodCalls(array $methodCalls)
    {
        foreach ($methodCalls as $methodCall) {
            $this->addMethodCall($methodCall);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param array|string $factory
     *
     * @return Definition
     */
    public function setFactory($factory)
    {
        if (is_string($factory)) {
            $factory = explode(':', $factory, 2);
        }

        $this->factory = $factory;

        return $this;
    }
}
