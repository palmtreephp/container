<?php

namespace Palmtree\Container;

use Palmtree\Container\Definition\Definition;
use Palmtree\Container\Exception\ParameterNotFoundException;
use Palmtree\Container\Exception\ServiceNotFoundException;
use Palmtree\Container\Exception\ServiceNotPublicException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** Regex for single parameters e.g '%my.parameter%' */
    const PATTERN_PARAMETER = '/^%([^%\s]+)%$/';
    /** Regex for multiple parameters in a string */
    const PATTERN_MULTI_PARAMETER = '/%%|%([^%\s]+)%/';

    /** @var Definition[] */
    private $definitions = [];
    /** @var mixed[] */
    private $services = [];
    /** @var array */
    private $parameters = [];

    /** @var array */
    private $envCache = [];

    public function __construct(array $definitions = [], array $parameters = [])
    {
        foreach ($definitions as $key => $definitionArgs) {
            $this->addDefinition($key, Definition::fromYaml($definitionArgs));
        }

        $this->parameters = $parameters;
        $this->parameters = $this->resolveArgs($this->parameters);
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
    public function hasDefinition($key)
    {
        return isset($this->definitions[$key]);
    }

    /**
     * @param string     $key
     * @param Definition $definition
     */
    public function addDefinition($key, Definition $definition)
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
     * Sets a parameter within the container.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws ParameterNotFoundException
     * @throws ServiceNotFoundException
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $this->resolveArg($value);
    }

    /**
     * Returns whether a parameter with the given key exists within the container.
     *
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
     * @return mixed
     * @throws ServiceNotFoundException
     */
    private function inject($key)
    {
        try {
            $this->get($key);
        } catch (ServiceNotPublicException $e) {
            // Ensures the service is created. Private services are allowed to be injected.
        }

        return $this->services[$key];
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
        $args = $this->resolveArgs($definition->getArguments());

        if ($factory = $definition->getFactory()) {
            list($class, $method) = $factory;
            $class   = $this->resolveArg($class);
            $method  = $this->resolveArg($method);
            $service = $class::$method(...$args);
        } else {
            $class   = $this->resolveArg($definition->getClass());
            $service = new $class(...$args);
        }

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
    private function resolveArgs(array $args)
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
     * @param mixed $arg
     *
     * @return mixed
     * @throws ParameterNotFoundException
     * @throws ServiceNotFoundException
     */
    private function resolveArg($arg)
    {
        if (!is_string($arg)) {
            return $arg;
        }

        if (strrpos($arg, '@') === 0) {
            return $this->inject(substr($arg, 1));
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
            }

            if ($envKey = $this->getEnvParameterKey($matches[1])) {
                return $this->getEnv($envKey);
            }

            return $this->getParameter($matches[1]);
        }, $arg);
    }

    /**
     * @param string $value
     *
     * @return null|string
     */
    private function getEnvParameterKey($value)
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
    private function getEnv($key)
    {
        if (isset($this->envCache[$key]) || array_key_exists($key, $this->envCache)) {
            return $this->envCache[$key];
        }

        $envVar = getenv($key);

        if (!$envVar) {
            try {
                $envVar = $this->resolveArg($this->getParameter("env($key)"));
            } catch (ParameterNotFoundException $exception) {
                // do nothing
            }
        }

        $this->envCache[$key] = $envVar;

        return $this->envCache[$key];
    }
}
