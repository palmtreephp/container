<?php

namespace Palmtree\Container\Tests\Fixtures;

use Palmtree\Container\Container;
use Palmtree\Container\ContainerAwareTrait;

class ContainerAwareTraitUser
{
    use ContainerAwareTrait;

    public function __construct(Container $container)
    {
        $this->setContainer($container);
    }
}
