<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\ContainerFactory;
use Palmtree\Container\Exception\ParameterNotFoundException;
use Palmtree\Container\Tests\Fixtures\Service\Foo;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    const FOO = 'Bar';

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
        \putenv('FOO=baz');
        $container = $this->createContainer();

        $this->assertEquals('baz', $container->getParameter('foo'));

        \putenv('FOO');
    }

    public function testConstantParameter()
    {
        $container = $this->createContainer();

        $this->assertSame(M_PI, $container->getParameter('pi'));
        $this->assertSame(self::FOO, $container->getParameter('constant_foo'));
    }

    public function testDefaultParameter()
    {
        $container = $this->createContainer();

        $obj = new \stdClass();

        $this->assertSame($obj, $container->getParameter('noop', $obj));
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

        $this->assertEquals('/path/to/zorb', $container->getParameter('multi_env'));
    }

    public function testMissingServicesKey()
    {
        $container = ContainerFactory::create(__DIR__ . '/fixtures/no_services.yml');

        $this->assertEquals('bar', $container->getParameter('foo'));
    }

    public function testParameterNotFoundException()
    {
        $this->expectException(ParameterNotFoundException::class);

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

        $this->assertEquals([1, 2, 3], $container->getParameter('compound'));
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
