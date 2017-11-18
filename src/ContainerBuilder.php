<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 16/11/2017
 * Time: 14:56
 */

namespace Palmtree\Container;


use Palmtree\Container\Definition\Definition;

class ContainerBuilder extends Container
{
    /** @var Definition[] */
    protected $definitions;

    public function findDefinition($key)
    {
        if (isset($this->definitions[$key])) {
            return $this->definitions[$key];
        }

        return null;
    }

    public function load($configFile)
    {

    }
}
