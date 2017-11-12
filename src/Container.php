<?php

namespace Palmtree\Container;

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

    protected $services;
    protected $parameters;

    public function __construct(array $services = [], array $parameters = [])
    {
        $this->parameters = $this->parseArgs($parameters);

        foreach ($services as $key => $service) {
            $this->add($key, $service);
        }
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

        if (is_array($service)) {
            $service = $this->create($service);
        }

        $this->services[$key] = $service;

        return $this->services[$key];
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

    protected function add($key, $service)
    {
        $this->services[$key] = $service;

        if (!isset($service['lazy']) || $service['lazy'] === true) {
            $this->get($key);
        }
    }

    /**
     * @param array $serviceArgs
     *
     * @return mixed
     */
    protected function create(array $serviceArgs)
    {
        $class = $serviceArgs['class'];

        if (isset($serviceArgs['arguments'])) {
            $args    = $this->parseArgs($serviceArgs['arguments']);
            $service = new $class(...$args);
        } else {
            $service = new $class;
        }

        if (isset($serviceArgs['calls'])) {
            foreach ($serviceArgs['calls'] as $call) {
                $method = $call['method'];
                $args   = isset($call['arguments']) ? $call['arguments'] : [];
                $service->$method(...$args);
            }
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
