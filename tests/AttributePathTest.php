<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests;

use Cloudstek\SCIM\FilterParser\AST;
use PHPUnit\Framework\TestCase;

class AttributePathTest extends TestCase
{
    public function testInstantiateWithoutNames()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Attribute path should contain at least one attribute name.');

        new AST\AttributePath(null, []);
    }

    public function testGetPath()
    {
        $attributePath = new AST\AttributePath('urn:foo:bar:2.0:baz', ['foo', 'bar']);

        $this->assertSame('foo.bar', $attributePath->getPath());
    }
}
