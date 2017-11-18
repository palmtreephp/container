<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\Container;
use Palmtree\Container\ContainerFactory;
use Palmtree\Container\Definition\Definition;
use Palmtree\Container\Tests\Fixtures\Bar;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testRegister()
    {
        $definition = new Definition();
        $definition->setClass(Bar::class);

        $container = new Container();

        $container->register('bar.test', $definition);

        $this->assertSame($definition, $container->getDefinition('bar.test'));
    }

    public function testCreate()
    {
        $definition = new Definition();
        $definition->setClass(Bar::class);

        $container = new Container();

        $container->register('bar.test', $definition);

        $container->build();

        $this->assertInstanceOf(Bar::class, $container->get('bar.test'));
    }

    public function testParameterExpansion()
    {
        $container = new Container([], [
            'foo' => 'bar',
            'baz' => '%foo%',
        ]);

        $this->assertEquals('bar', $container->getParameter('baz'));
    }

    public function testEnvParameterExpansion()
    {
        putenv('PHP_FOO=bar');

        $container = new Container([], [
            'foo' => '%env(PHP_FOO)%',
        ]);

        $this->assertEquals('bar', $container->getParameter('foo'));
    }

    public function testDependencyInjection()
    {
        $container = ContainerFactory::create(__DIR__ . '/fixtures/config/config.yml');

        $this->assertSame($container->get('bar.test'), $container->get('foo.test')->getBar());
        $this->assertEquals('Baz!', $container->get('foo.test')->getBaz());
    }

    public function testMethodCalls()
    {
        $container = ContainerFactory::create(__DIR__ . '/fixtures/config/config.yml');

        $this->assertEquals('Foo!', $container->get('baz.test')->getFoo());
    }

    public function testLazyLoading()
    {
        Bar::$instances = 0;

        $definition = new Definition();
        $definition->setClass(Bar::class);
        $definition->setLazy(true);

        $container = new Container();
        $container->register('bar.test', $definition);
        $container->build();

        $this->assertEquals(0, Bar::$instances);
        $this->assertInstanceOf(Bar::class, $container->get('bar.test'));
        $this->assertEquals(1, Bar::$instances);
    }
}
