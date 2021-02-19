<?php

namespace Palmtree\Container;

use Palmtree\Container\Exception\ParameterNotFoundException;
use Palmtree\Container\Exception\ServiceNotFoundException;
use Palmtree\Container\Exception\ServiceNotPublicException;

class Resolver
{
    /** Regex for single parameters e.g '%my.parameter%' */
    private const PATTERN_PARAMETER = '/^%([^%\s]+)%$/';
    /** Regex for multiple parameters in a string */
    private const PATTERN_MULTI_PARAMETER = '/%%|%([^%\s]+)%/';
    /** @var Container */
    private $container;
    /** @var array */
    private $containerServices;
    /** @var array */
    private $envCache = [];

    public function __construct(Container $container, array &$containerServices)
    {
        $this->container         = $container;
        $this->containerServices = &$containerServices;
    }

    public function resolveArgs(array $args): array
    {
        foreach ($args as $key => &$arg) {
            if (\is_array($arg)) {
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
     *
     * @throws ServiceNotFoundException
     * @throws ParameterNotFoundException
     */
    public function resolveArg($arg)
    {
        if (!\is_string($arg) || $arg === '') {
            return $arg;
        }

        if ($arg[0] === '@') {
            return $this->inject(\substr($arg, 1));
        }

        return $this->resolveParameter($arg);
    }

    /**
     * @param string $key
     *
     * @return mixed
     *
     * @throws ServiceNotFoundException
     */
    private function inject(string $key)
    {
        try {
            $this->container->get($key);
        } catch (ServiceNotPublicException $e) {
            // Ensures the service is created. Private services are allowed to be injected.
        }

        return $this->containerServices[$key];
    }

    /**
     * @param string $arg
     *
     * @return mixed
     *
     * @throws ParameterNotFoundException
     */
    private function resolveParameter($arg)
    {
        // Resolve a single parameter value e.g %my_param%
        // Used for non-string values (boolean, integer etc)
        if (\preg_match(self::PATTERN_PARAMETER, $arg, $matches)) {
            $envKey = $this->getEnvParameterKey($matches[1]);

            if ($envKey !== null) {
                return $this->getEnv($envKey);
            }

            $constKey = $this->getConstParameterKey($matches[1]);

            if ($constKey !== null) {
                return \constant($constKey);
            }

            return $this->container->getParameter($matches[1]);
        }

        // Resolve multiple parameters in a string e.g /%parent_dir%/somewhere/%child_dir%
        return \preg_replace_callback(self::PATTERN_MULTI_PARAMETER, function ($matches) {
            // Skip %% to allow escaping percent signs
            if (!isset($matches[1])) {
                return '%';
            }

            if ($envKey = $this->getEnvParameterKey($matches[1])) {
                return $this->getEnv($envKey);
            }

            if ($constKey = $this->getConstParameterKey($matches[1])) {
                return \constant($constKey);
            }

            return $this->container->getParameter($matches[1]);
        }, $arg);
    }

    private function getEnvParameterKey(string $value): ?string
    {
        if (\strpos($value, 'env(') === 0 && \substr($value, -1) === ')' && $value !== 'env()') {
            return \substr($value, 4, -1);
        }

        return null;
    }

    private function getConstParameterKey(string $value): ?string
    {
        if (\strpos($value, 'constant(') === 0 && \substr($value, -1) === ')' && $value !== 'constant()') {
            return \substr($value, 9, -1);
        }

        return null;
    }

    /**
     * @param string $key
     *
     * @return string|bool
     */
    private function getEnv(string $key)
    {
        if (isset($this->envCache[$key]) || \array_key_exists($key, $this->envCache)) {
            return $this->envCache[$key];
        }

        if (!$envVar = \getenv($key)) {
            try {
                $envVar = $this->resolveArg($this->container->getParameter("env($key)"));
            } catch (ParameterNotFoundException $exception) {
                // do nothing
            }
        }

        $this->envCache[$key] = $envVar;

        return $this->envCache[$key];
    }
}
