<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\Container;
use Palmtree\Container\Definition\Definition;
use Palmtree\Container\Definition\MethodCall;
use Palmtree\Container\Exception\InvalidDefinitionException;
use Palmtree\Container\Tests\Fixtures\Service\PhpDefinedService;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{
    public function testInvalidDefinitionException()
    {
        $this->expectException(InvalidDefinitionException::class);

        Definition::fromYaml([]);
    }

    public function testPhpDefinitionCreation()
    {
        $container  = new Container();
        $definition = new Definition();

        $methodCall = new MethodCall();
        $methodCall->setName('setString')->setArguments(['foo']);

        $arg = ['foo' => 'bar'];

        $definition
            ->setClass(PhpDefinedService::class)
            ->setArguments([$arg])
            ->setMethodCalls([$methodCall]);

        $container->addDefinition('php_defined_service', $definition);

        $this->assertInstanceOf(PhpDefinedService::class, $container->get('php_defined_service'));

        $this->assertEquals('foo', $container->get('php_defined_service')->getString());
        $this->assertSame(['foo' => 'bar'], $container->get('php_defined_service')->getArgs());
    }
}
