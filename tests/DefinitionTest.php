<?php

namespace Palmtree\Container\Tests;

use Palmtree\Container\Definition\Definition;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{
    /** @expectedException \Palmtree\Container\Exception\InvalidDefinitionException */
    public function testInvalidDefinitionException()
    {
        Definition::fromYaml([]);
    }
}
