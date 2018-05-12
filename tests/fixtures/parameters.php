<?php

/** @var \Palmtree\Container\Container $container */

$container->setParameter('time', time());
$container->setParameter('foo2', '%foo%');
$container->setParameter('foo_service', '@foo');
