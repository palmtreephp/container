<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\Container;
use Palmtree\Container\ContainerFactory;
use Palmtree\Container\Tests\Fixtures\Service\Foo;
use PHPUnit\Framework\TestCase;

class ServiceAutowireTest extends TestCase
{
    public function testAutowiring(): void
    {
        $container = $this->createContainer();

        $this->assertInstanceOf(Foo::class, $container->get(Foo::class));
    }

    private function createContainer(): Container
    {
        return ContainerFactory::create(__DIR__ . '/fixtures/autowiring.yml');
    }
}
