<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\ContainerFactory;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testHasParameter()
    {
        $container = $this->createContainer();
        $this->assertTrue($container->hasParameter('foo'));
        $this->assertFalse($container->hasParameter('noop'));
    }

    public function testParameterReturnTypes()
    {
        $container = $this->createContainer();
        $this->assertTrue($container->getParameter('bool'));
        $this->assertSame(1, $container->getParameter('one'));
    }

    public function testDefaultEnvParameter()
    {
        $container = $this->createContainer();
        $this->assertEquals('zorb', $container->getParameter('foo'));
    }

    public function testEnvParameter()
    {
        putenv('FOO=baz');
        $container = $this->createContainer();

        $this->assertEquals('baz', $container->getParameter('foo'));
        putenv('FOO');
    }

    /**
     * @return \Palmtree\Container\Container
     */
    private function createContainer()
    {
        $container = ContainerFactory::create(__DIR__ . '/fixtures/config.yml');

        return $container;
    }
}
