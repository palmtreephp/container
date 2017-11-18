<?php

namespace Palmtree\Container;

use Palmtree\Container\Definition\Definition;
use Palmtree\Container\Exception\ContainerBuiltException;
use Palmtree\Container\Exception\DefinitionNotFoundException;
use Palmtree\Container\Exception\ParameterNotFoundException;
use Palmtree\Container\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** @var array */
    protected $services;
    /** @var array */
    protected $parameters;
    /** @var bool */
    protected $built = false;
    /** @var  Resolver */
    protected $resolver;

    public function __construct(array $services = [], array $parameters = [])
    {
        $this->resolver = new Resolver($this);

        $this->parameters = $parameters;

        $this->resolver->resolve($this->parameters);

        foreach ($services as $id => $definitionArgs) {
            $this->register($id, Definition::fromArray($definitionArgs));
        }
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     * @throws ParameterNotFoundException
     */
    public function getParameter($key, $default = null)
    {
        if (!array_key_exists($key, $this->parameters)) {
            if (func_num_args() < 2) {
                throw new ParameterNotFoundException($key);
            }

            return $default;
        }

        return $this->parameters[$key];
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ContainerBuiltException
     * @throws DefinitionNotFoundException
     */
    public function getDefinition($id)
    {
        if ($this->isBuilt()) {
            throw new ContainerBuiltException("Impossible to get Definition object. Container already built.");
        }

        if (!$this->has($id)) {
            throw new DefinitionNotFoundException($id);
        }

        if (!$this->services[$id] instanceof Definition) {
            throw new DefinitionNotFoundException("Impossible to get Definition object. Service already created.");
        }

        return $this->services[$id];
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id, $this->services);
    }

    /**
     * @param string $id
     *
     * @return mixed
     * @throws ServiceNotFoundException
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException($id);
        }

        if ($this->services[$id] instanceof Definition) {
            $this->services[$id] = $this->create($this->services[$id]);
        }

        return $this->services[$id];
    }

    public function build()
    {
        if ($this->isBuilt()) {
            throw new ContainerBuiltException("Container already built.");
        }

        foreach ($this->services as $id => $definition) {
            if ($definition instanceof Definition && !$definition->isLazy()) {
                $this->get($id);
            }
        }

        $this->built = true;
    }

    public function isBuilt()
    {
        return $this->built;
    }

    /**
     * @param string $id
     * @param Definition $definition
     *
     * @return Definition
     */
    public function register($id, Definition $definition)
    {
        $this->services[$id] = $definition;

        return $this->services[$id];
    }

    /**
     * Creates a service as defined by the Definition object.
     *
     * @param Definition $definition
     *
     * @return mixed
     */
    protected function create(Definition $definition)
    {
        $class = $definition->getClass();
        $args = $this->resolver->resolve($definition->getArguments());

        $service = new $class(...$args);

        foreach ($definition->getMethodCalls() as $methodCall) {
            $methodName = $methodCall->getName();
            $methodArgs = $this->resolver->resolve($methodCall->getArguments());
            $service->$methodName(...$methodArgs);
        }

        return $service;
    }
}
