<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/ExampleDependency.php';
require_once __DIR__ . '/ExampleService.php';

use Palmtree\Container\ContainerFactory;

$container = ContainerFactory::create(__DIR__ . '/config.yml', false);

$definition = $container->getDefinition('my_service');
//$definition->replaceArgument(2, '%some_other_param%');

$container->build();

$container->get('my_service')->doThing();

$container->getParameter('some_param');
