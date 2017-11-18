<?php

namespace Palmtree\Container;

use Palmtree\Container\Exception\InvalidReferenceException;

class Resolver
{
    /** @var Container */
    protected $container;

    /**
     * Resolver constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $args
     * @return array
     * @throws InvalidReferenceException
     */
    public function resolve(array &$args)
    {
        foreach ($args as &$value) {
            if (is_array($value)) {
                $this->resolve($value);
            } else {
                $resolved = $this->resolveService($value);
                if (!$resolved) {
                    $resolved = $this->resolveParameter($value);
                }
            }
        }

        return $args;
    }

    /**
     * Attempts to replace (by reference) the given parameter key with its actual value.
     *
     * If the value looks like an environment variable e.g '%env(MY_ENV_VAR)%' we call getenv(MY_ENV_VAR). Otherwise
     * we pass the key to @see Container::getParameter()
     *
     * @param string $value The parameter reference e.g '%my_param%' or '%env(MY_ENV_VAR)%'
     * @return bool Whether the parameter was resolved or not.
     */
    protected function resolveParameter(&$value)
    {
        if (preg_match('/^%(env\(([^\)]+)\)|[^%]+)%$/', $value, $matches)) {
            if (!empty($matches[2])) {
                $value = getenv($matches[2]);
            } else {
                $value = $this->container->getParameter($matches[1]);
            }

            return true;
        }

        return false;
    }

    /**
     * Attempts to replace (by reference) the given service id with an instance of that service.
     *
     * e.g: '@my_service' should be replaced by an instance of the MyService class.
     *
     * @param string $key The service reference e.g '@my_service'
     * @param mixed $value The value passed by reference.
     * @return bool Whether the service was resolved or not.
     */
    protected function resolveService(&$value)
    {
        if (preg_match('/^@(.+)$/', $value, $matches)) {
            $value = $this->container->get($matches[1]);

            return true;
        }

        return false;
    }
}
