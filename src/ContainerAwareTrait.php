<?php

namespace Palmtree\Container;

trait ContainerAwareTrait
{
    /** @var Container */
    protected $container;

    protected function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->container->get($key);
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParameter(string $key, $default = null)
    {
        return $this->container->getParameter($key, $default);
    }
}
