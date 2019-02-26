<?php

namespace Palmtree\Container;

use Palmtree\Container\Definition\Definition;
use Palmtree\Container\Exception\ParameterNotFoundException;
use Palmtree\Container\Exception\ServiceNotFoundException;
use Palmtree\Container\Exception\ServiceNotPublicException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** @var Definition[] */
    private $definitions = [];
    /** @var mixed[] */
    private $services = [];
    /** @var array */
    private $parameters = [];
    /** @var Resolver */
    private $resolver;

    public function __construct(array $definitions = [], array $parameters = [])
    {
        foreach ($definitions as $key => $definitionArgs) {
            $this->addDefinition($key, Definition::fromYaml($definitionArgs, $key));
        }

        $this->resolver = new Resolver($this, $this->services);

        $this->parameters = $parameters;
        $this->parameters = $this->resolver->resolveArgs($this->parameters);
    }

    /**
     * Instantiates non-lazy services.
     */
    public function instantiateServices()
    {
        foreach ($this->definitions as $key => $definition) {
            if (!$definition->isLazy()) {
                $this->services[$key] = $this->create($definition);
            }
        }
    }

    /**
     * Returns whether a service with the given key exists within the container.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->services[$key]);
    }

    /**
     * Returns a service object with the given key.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws ServiceNotFoundException
     * @throws ServiceNotPublicException
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            if (!$this->hasDefinition($key)) {
                throw new ServiceNotFoundException($key);
            }

            $this->services[$key] = $this->create($this->definitions[$key]);
        }

        if (!$this->definitions[$key]->isPublic()) {
            throw new ServiceNotPublicException($key);
        }

        return $this->services[$key];
    }

    /**
     * Returns whether a definition with the given key exists within the container.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasDefinition(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    public function addDefinition(string $key, Definition $definition)
    {
        $this->definitions[$key] = $definition;
    }

    /**
     * Returns a parameter with the given key or a default value if given.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     *
     * @throws ParameterNotFoundException
     */
    public function getParameter(string $key, $default = null)
    {
        if (!$this->hasParameter($key)) {
            if (\func_num_args() < 2) {
                throw new ParameterNotFoundException($key);
            }

            return $default;
        }

        return $this->parameters[$key];
    }

    /**
     * Sets a parameter within the container.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws ParameterNotFoundException
     * @throws ServiceNotFoundException
     */
    public function setParameter(string $key, $value)
    {
        $this->parameters[$key] = $this->resolver->resolveArg($value);
    }

    /**
     * Returns whether a parameter with the given key exists within the container.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter(string $key): bool
    {
        return isset($this->parameters[$key]) || \array_key_exists($key, $this->parameters);
    }

    /**
     * Creates a service as defined by the Definition object.
     *
     * @param Definition $definition
     *
     * @return mixed
     */
    private function create(Definition $definition)
    {
        $args = $this->resolver->resolveArgs($definition->getArguments());

        if ($factory = $definition->getFactory()) {
            list($class, $method) = $factory;

            $class   = $this->resolver->resolveArg($class);
            $method  = $this->resolver->resolveArg($method);
            $service = $class::$method(...$args);
        } else {
            $class   = $this->resolver->resolveArg($definition->getClass());
            $service = new $class(...$args);
        }

        foreach ($definition->getMethodCalls() as $methodCall) {
            $methodName = $methodCall->getName();
            $methodArgs = $this->resolver->resolveArgs($methodCall->getArguments());
            $service->$methodName(...$methodArgs);
        }

        return $service;
    }
}
