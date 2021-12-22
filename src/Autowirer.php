<?php

namespace Palmtree\Container;

use Palmtree\Container\Definition\Definition;

class Autowirer
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function wire(Definition $definition, array $args): array
    {
        $reflClass = new \ReflectionClass($definition->getClass());
        $params    = $reflClass->getConstructor()->getParameters();

        if (\count($params) > \count($args)) {
            foreach ($params as $key => $param) {
                if ($param->hasType()) {
                    $type = $param->getType();
                    if (!$type->isBuiltin()) {
                        $class = $type->getName();
                        if ($this->container->has($class)) {
                            array_splice($args, $key, 0, [$this->container->get($class)]);
                        }
                    }
                }
            }
        }

        return $args;
    }
}
