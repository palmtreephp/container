<?php

namespace Palmtree\Container;

use Palmtree\Container\Exception\InvalidReferenceException;

class Resolver
{
    /** Regex for parameters e.g '%my.parameter%' */
    const PATTERN_PARAMETER = '/^%([^%]+)%$/';
    /** Sub Regex for parameters matching environment variables e.g '%env(MY_ENV_VAR)%' */
    const PATTERN_ENV_PARAMETER = '/^env\(([^\)]+)\)$/';
    /** Regex for services e.g '@myservice' */
    const PATTERN_SERVICE = '/^@(.+)$/';

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
        foreach ($args as &$reference) {
            if (is_array($reference)) {
                $this->resolve($reference);
            } elseif (!($this->resolveService($reference) || $this->resolveParameter($reference))) {
                throw new InvalidReferenceException("Unable to resolve reference $reference");
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
     * @param string $arg The parameter key e.g '%my_param%' or '%env(MY_ENV_VAR)%'
     * @return bool Whether the parameter was resolved or not.
     */
    protected function resolveParameter(&$arg)
    {
        if (preg_match(static::PATTERN_PARAMETER, $arg, $matches)) {
            if (preg_match(static::PATTERN_ENV_PARAMETER, $matches[1], $envMatches)) {
                $arg = getenv($envMatches[1]);
            } else {
                $arg = $this->container->getParameter($matches[1]);
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
     * @param string $arg The service reference e.g '@my_service'
     * @return bool Whether the service was resolved or not.
     */
    protected function resolveService(&$arg)
    {
        if (preg_match(static::PATTERN_SERVICE, $arg, $matches)) {
            $arg = $this->container->get($matches[1]);

            return true;
        }

        return false;
    }
}
