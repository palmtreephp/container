<?php

namespace Palmtree\Container;

use Palmtree\Container\Definition\Definition;
use Palmtree\Container\Exception\ParameterNotFoundException;
use Palmtree\Container\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** Regex for parameters e.g '%my.parameter%' */
    const PATTERN_PARAMETER = '/^%([^%]+)%$/';
    /** Regex for environment variable parameters e.g '%env(MY_ENV_VAR)%' */
    const PATTERN_ENV_PARAMETER = '/^env\(([^\)]+)\)$/';
    /** Regex for services e.g '@myservice' */
    const PATTERN_SERVICE = '/^@(.+)$/';

    /** @var Definition[]|mixed[] */
    protected $services;
    /** @var array */
    protected $parameters;

    public function __construct(array $services = [], array $parameters = [])
    {
        $this->parameters = $this->parseArgs($parameters);

        foreach ($services as $key => $definitionArgs) {
            $this->add($key, $definitionArgs);
        }
    }

    /**
     * @param string $key
     * @param mixed  $default
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
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->services);
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws ServiceNotFoundException
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new ServiceNotFoundException($key);
        }

        $service = $this->services[$key];

        if ($service instanceof Definition) {
            $service = $this->create($service);
        }

        $this->services[$key] = $service;

        return $this->services[$key];
    }

    /**
     * @param string $key
     * @param array  $definitionArgs
     *
     * @return Definition
     */
    protected function add($key, array $definitionArgs)
    {
        $definition = Definition::fromYaml($definitionArgs);

        $this->services[$key] = $definition;

        if (!$definition->isLazy()) {
            $this->get($key);
        }

        return $definition;
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
        $args  = $this->parseArgs($definition->getArguments());

        $service = new $class(...$args);

        foreach ($definition->getMethodCalls() as $methodCall) {
            $methodName = $methodCall->getName();
            $methodArgs = $this->parseArgs($methodCall->getArguments());
            $service->$methodName(...$methodArgs);
        }

        return $service;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    protected function parseArgs(array $args)
    {
        $matches = [];

        foreach ($args as $key => $arg) {
            if (is_array($arg)) {
                $args[$key] = $this->parseArgs($arg);
            } elseif (preg_match(static::PATTERN_PARAMETER, $arg, $matches)) {
                $parameter = $matches[1];

                $envMatches = [];
                if (preg_match(static::PATTERN_ENV_PARAMETER, $parameter, $envMatches)) {
                    $args[$key] = getenv($envMatches[1]);
                } else {
                    $args[$key] = $this->getParameter($parameter);
                }
            } elseif (preg_match(static::PATTERN_SERVICE, $arg, $matches)) {
                $args[$key] = $this->get($matches[1]);
            }
        }

        return $args;
    }
}
