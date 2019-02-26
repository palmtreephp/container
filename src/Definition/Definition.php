<?php

namespace Palmtree\Container\Definition;

use Palmtree\Container\Exception\InvalidDefinitionException;

class Definition
{
    /** @var string */
    private $class;
    /** @var bool */
    private $lazy = false;
    /** @var bool */
    private $public = true;
    /** @var array */
    private $arguments = [];
    /** @var MethodCall[] */
    private $methodCalls = [];
    /** @var array */
    private $factory;

    /**
     * @param array|null  $yaml
     * @param string|null $key
     *
     * @return Definition
     *
     * @throws InvalidDefinitionException
     */
    public static function fromYaml($yaml, ?string $key = null): self
    {
        if (!isset($yaml['class']) && !isset($yaml['factory'])) {
            if ($key !== null) {
                $yaml['class'] = $key;
            } else {
                throw new InvalidDefinitionException("Missing required 'class' argument. Must be a FQCN.");
            }
        }

        $definition = new self();

        $definition->setClass($yaml['class'] ?? null)
                   ->setFactory($yaml['factory'] ?? [])
                   ->setLazy($yaml['lazy'] ?? false)
                   ->setPublic($yaml['public'] ?? true)
                   ->setArguments($yaml['arguments'] ?? []);

        foreach ($yaml['calls'] ?? [] as $call) {
            $methodCall = MethodCall::fromYaml($call);
            $definition->addMethodCall($methodCall);
        }

        return $definition;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function isLazy(): bool
    {
        return $this->lazy;
    }

    public function setLazy(bool $lazy): self
    {
        $this->lazy = $lazy;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function addMethodCall(MethodCall $methodCall): self
    {
        $this->methodCalls[] = $methodCall;

        return $this;
    }

    /**
     * @return MethodCall[]
     */
    public function getMethodCalls(): array
    {
        return $this->methodCalls;
    }

    /**
     * @param MethodCall[] $methodCalls
     *
     * @return Definition
     */
    public function setMethodCalls(array $methodCalls): self
    {
        foreach ($methodCalls as $methodCall) {
            $this->addMethodCall($methodCall);
        }

        return $this;
    }

    public function getFactory(): ?array
    {
        return $this->factory;
    }

    /**
     * @param array|string $factory
     *
     * @return Definition
     */
    public function setFactory($factory): self
    {
        if (\is_string($factory)) {
            $factory = \explode(':', $factory, 2);
        }

        $this->factory = $factory;

        return $this;
    }
}
