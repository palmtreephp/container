<?php

namespace Palmtree\Container\Definition;

class MethodCall
{
    /** @var string */
    protected $name;
    /** @var array */
    protected $arguments = [];

    public static function fromYaml(array $yaml): self
    {
        $methodCall = new self();

        $methodCall->setName($yaml['method'])
                   ->setArguments($yaml['arguments'] ?? []);

        return $methodCall;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }
}
