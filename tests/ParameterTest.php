<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\ContainerFactory;
use Palmtree\Container\Tests\Fixtures\Service\Foo;
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

    public function testPhpParameters()
    {
        $container = $this->createContainer();

        $this->assertInstanceOf(Foo::class, $container->getParameter('foo_service'));
        $this->assertInstanceOf(\DateTime::class, $container->getParameter('time'));
    }

    public function testMultipleParameterString()
    {
        $container = $this->createContainer();

        $this->assertEquals('/path/to/some/file', $container->getParameter('multi'));
    }

    /** @expectedException \Palmtree\Container\Exception\ParameterNotFoundException */
    public function testParameterNotFoundException()
    {
        $container = $this->createContainer();

        $container->getParameter('noop');
    }

    public function testEscapedPercentSign()
    {
        $container = $this->createContainer();

        $this->assertEquals('%Hello%', $container->getParameter('escaped_percent'));
    }

    public function testCompoundParameter()
    {
        $container = $this->createContainer();

        $this->assertEquals([1,2,3], $container->getParameter('compound'));
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
