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
    protected $services = [];
    /** @var array */
    protected $parameters = [];

    /** @var array */
    protected $envCache = [];

    public function __construct(array $services = [], array $parameters = [])
    {
        foreach ($services as $key => $definitionArgs) {
            $this->add($key, $definitionArgs);
        }

        $this->parameters = $parameters;
        $this->parameters = $this->resolveArgs($this->parameters);

        // Instantiate non-lazy services
        foreach ($this->services as $key => $service) {
            if ($service instanceof Definition && !$service->isLazy()) {
                $this->get($key);
            }
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
        if (!$this->hasParameter($key)) {
            if (func_num_args() < 2) {
                throw new ParameterNotFoundException($key);
            }

            return $default;
        }

        return $this->parameters[$key];
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws ParameterNotFoundException
     * @throws ServiceNotFoundException
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $this->resolveArg($value);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter($key)
    {
        return isset($this->parameters[$key]) || array_key_exists($key, $this->parameters);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->services[$key]) || array_key_exists($key, $this->services);
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

        return $this->services[$key] = $service;
    }

    /**
     * @param string $key
     * @param array  $definitionArgs
     *
     * @return Definition
     */
    protected function add($key, array $definitionArgs)
    {
        return $this->services[$key] = Definition::fromYaml($definitionArgs);
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
        $args  = $this->resolveArgs($definition->getArguments());

        $service = new $class(...$args);

        foreach ($definition->getMethodCalls() as $methodCall) {
            $methodName = $methodCall->getName();
            $methodArgs = $this->resolveArgs($methodCall->getArguments());
            $service->$methodName(...$methodArgs);
        }

        return $service;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    protected function resolveArgs(array $args)
    {
        foreach ($args as $key => &$arg) {
            if (is_array($arg)) {
                $arg = $this->resolveArgs($arg);
            } else {
                $arg = $this->resolveArg($arg);
            }
        }

        return $args;
    }

    /**
     * @param $arg
     *
     * @return mixed|string
     * @throws ParameterNotFoundException
     * @throws ServiceNotFoundException
     */
    protected function resolveArg($arg)
    {
        if (is_string($arg)) {
            if (preg_match(static::PATTERN_PARAMETER, $arg, $matches)) {
                $parameter = $matches[1];

                if (preg_match(static::PATTERN_ENV_PARAMETER, $parameter, $envMatches)) {
                    $arg = $this->getEnv($envMatches[1]);
                } else {
                    $arg = $this->getParameter($parameter);
                }
            } elseif (preg_match(static::PATTERN_SERVICE, $arg, $matches)) {
                $arg = $this->get($matches[1]);
            }
        }

        return $arg;
    }

    /**
     * @param string $key
     *
     * @return string|bool
     */
    protected function getEnv($key)
    {
        if (isset($this->envCache[$key]) || array_key_exists($key, $this->envCache)) {
            return $this->envCache[$key];
        }

        $envVar = getenv($key);

        if (!$envVar) {
            try {
                $envVar = $this->getParameter(sprintf('env(%s)', $key));
            } catch (ParameterNotFoundException $exception) {
                // do nothing
            }
        }

        $this->envCache[$key] = $this->resolveArg($envVar);

        return $this->envCache[$key];
    }
}
