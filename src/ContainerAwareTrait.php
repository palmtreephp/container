<?php

namespace Palmtree\Container;

trait ContainerAwareTrait
{
    /** @var Container $container */
    protected $container;

    protected function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->container->get($key);
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getParameter($key, $default = null)
    {
        return $this->container->getParameter($key, $default);
    }
}
