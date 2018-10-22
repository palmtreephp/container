<?php

namespace Palmtree\Container;

use Palmtree\Container\Definition\Definition;
use Palmtree\Container\Exception\ParameterNotFoundException;
use Palmtree\Container\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** Regex for single parameters e.g '%my.parameter%' */
    const PATTERN_PARAMETER = '/^%([^%\s]+)%$/';
    /** Regex for multiple parameters in a string */
    const PATTERN_MULTI_PARAMETER = '/%%|%([^%\s]+)%/';
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
    }

    /**
     * Instantiates non-lazy services.
     *
     * @throws ServiceNotFoundException
     */
    public function instantiateServices()
    {
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
        $class = $this->resolveArg($definition->getClass());
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
        if (!is_string($arg)) {
            return $arg;
        }

        if (preg_match(self::PATTERN_SERVICE, $arg, $matches)) {
            return $this->get($matches[1]);
        }

        // Resolve a single parameter value e.g %my_param%
        // Used for non-string values (boolean, integer etc)
        if (preg_match(self::PATTERN_PARAMETER, $arg, $matches)) {
            $envKey = $this->getEnvParameterKey($matches[1]);

            if (!is_null($envKey)) {
                return $this->getEnv($envKey);
            }

            return $this->getParameter($matches[1]);
        }

        // Resolve multiple parameters in a string e.g /%parent_dir%/somewhere/%child_dir%
        return preg_replace_callback(self::PATTERN_MULTI_PARAMETER, function ($matches) {
            // Skip %% to allow escaping percent signs
            if (!isset($matches[1])) {
                return '%';
            } elseif ($envKey = $this->getEnvParameterKey($matches[1])) {
                return $this->getEnv($envKey);
            } else {
                return $this->getParameter($matches[1]);
            }
        }, $arg);

        return $arg;
    }

    /**
     * @param string $value
     *
     * @return null|string
     */
    protected function getEnvParameterKey($value)
    {
        if (strpos($value, 'env(') === 0 && substr($value, -1) === ')' && $value !== 'env()') {
            return substr($value, 4, -1);
        }

        return null;
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
