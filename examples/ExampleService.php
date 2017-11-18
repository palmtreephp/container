<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 16/11/2017
 * Time: 14:23
 */

namespace Palmtree\Container\Example;


class ExampleService
{
    protected $dependency;
    protected $config;
    protected $someParam;

    public function __construct(ExampleDependency $dependency, array $config, $someParam)
    {
        $this->dependency = $dependency;
        $this->config = $config;
        $this->someParam = $someParam;
    }

    public function doLoadThing($number)
    {
    }

    public function doThing()
    {
        echo "Doing things!";
    }
}
