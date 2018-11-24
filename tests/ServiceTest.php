<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\Container;
use Palmtree\Container\ContainerFactory;
use Palmtree\Container\Tests\Fixtures\Service\Bar;
use Palmtree\Container\Tests\Fixtures\Service\Foo;
use Palmtree\Container\Tests\Fixtures\Service\LazyLoad;
use Palmtree\Container\Tests\Fixtures\Service\PrivateService;
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

    public function testPrivateService()
    {
        $container = $this->createContainer();

        $consumer = $container->get('private_service_consumer');

        $this->assertInstanceOf(PrivateService::class, $consumer->getPrivateService());
    }

    public function testFactory()
    {
        $container = $this->createContainer();

        $foo = $container->get('foo_from_factory');

        $this->assertInstanceOf(Foo::class, $foo);
    }

    /** @expectedException \Palmtree\Container\Exception\ServiceNotPublicException */
    public function testServiceNotPublicException()
    {
        $container = $this->createContainer();

        $container->get('private_service');
    }

    /** @expectedException \Palmtree\Container\Exception\ServiceNotFoundException */
    public function testParameterNotFoundException()
    {
        $container = $this->createContainer();

        $container->get('noop');
    }

    public function testMissingParametersKey()
    {
        $container = ContainerFactory::create(__DIR__ . '/fixtures/no_parameters.yml');

        $this->assertInstanceOf(Bar::class, $container->get('bar'));
    }

    public function testEmptyConfig()
    {
        $container = ContainerFactory::create(__DIR__ . '/fixtures/empty_config.yml');

        $this->assertInstanceOf(Container::class, $container);
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
