<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\Container;
use Palmtree\Container\ContainerFactory;
use Palmtree\Container\Tests\Fixtures\ContainerAwareTraitUser;
use Palmtree\Container\Tests\Fixtures\Service\Foo;
use PHPUnit\Framework\TestCase;

class ContainerAwareTraitTest extends TestCase
{
    public function testTraitContainer()
    {
        $traitUser = $this->createTraitUser();

        $this->assertInstanceOf(Container::class, $traitUser->getContainer());
    }

    public function testTraitGetParameter()
    {
        $traitUser = $this->createTraitUser();

        $this->assertEquals(1, $traitUser->getParameter('one'));
    }

    public function testTraitGetService()
    {
        $traitUser = $this->createTraitUser();

        $this->assertInstanceOf(Foo::class, $traitUser->get('foo'));
    }

    /**
     * @return ContainerAwareTraitUser
     */
    private function createTraitUser()
    {
        $container = ContainerFactory::create(__DIR__ . '/fixtures/config.yml');

        $traitUser = new ContainerAwareTraitUser($container);

        return $traitUser;
    }
}
