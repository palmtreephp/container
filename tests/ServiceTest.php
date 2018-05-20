<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\ContainerFactory;
use Palmtree\Container\Tests\Service\Foo;
use Palmtree\Container\Tests\Service\LazyLoad;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    public function testDependencyInjection()
    {
        $container = $this->createContainer();

        $this->assertSame($container->get('foo')->getBar(), $container->get('bar'));
    }

    public function testLazyLoad()
    {
        $container = $this->createContainer();

        $this->assertEquals(0, LazyLoad::$instances);

        $container->get('lazy_load');

        $this->assertEquals(1, LazyLoad::$instances);
    }

    public function testMethodCall()
    {
        $container = $this->createContainer();

        $this->assertTrue($container->get('foo')->getBaz());
    }

    public function testDependencyDefinedAfterDefinition()
    {
        $container = $this->createContainer();

        $this->assertInstanceOf(Foo::class, $container->get('baz')->getFoo());
    }


    /** @expectedException \Palmtree\Container\Exception\ServiceNotFoundException */
    public function testParameterNotFoundException()
    {
        $container = $this->createContainer();

        $container->get('noop');
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
