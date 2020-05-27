<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests\AST;

use Cloudstek\SCIM\FilterParser\AST;
use PHPUnit\Framework\TestCase;

/**
 * Attribute path test.
 *
 * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
 */
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

    public function testToString()
    {
        $attributePath = new AST\AttributePath('urn:foo:bar:2.0:baz', ['foo', 'bar']);

        $this->assertSame('foo.bar', (string)$attributePath);
    }

    public function testGetNames()
    {
        $attributePath = new AST\AttributePath('urn:foo:bar:2.0:baz', ['foo', 'bar']);

        $this->assertEquals(['foo', 'bar'], $attributePath->getNames());
    }

    public function testCountable()
    {
        $attributePath = new AST\AttributePath('urn:foo:bar:2.0:baz', ['foo', 'bar']);

        $this->assertCount(2, $attributePath);
    }

    public function testArrayAccess()
    {
        $attributePath = new AST\AttributePath('urn:foo:bar:2.0:baz', ['foo', 'bar']);

        $this->assertTrue(isset($attributePath[0]));
        $this->assertSame('foo', $attributePath[0]);

        $this->assertTrue(isset($attributePath[1]));
        $this->assertSame('bar', $attributePath[1]);
    }

    public function testArrayAccessSetException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Attribute path is read-only.');

        $attributePath = new AST\AttributePath('urn:foo:bar:2.0:baz', ['foo', 'bar']);

        $attributePath[0] = 'baz';
    }

    public function testArrayAccessUnsetException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Attribute path is read-only.');

        $attributePath = new AST\AttributePath('urn:foo:bar:2.0:baz', ['foo', 'bar']);

        unset($attributePath[0]);
    }

    public function testIterator()
    {
        $attributePath = new AST\AttributePath('urn:foo:bar:2.0:baz', ['foo', 'bar']);
        $names = [];

        foreach ($attributePath as $name) {
            $names[] = $name;
        }

        $this->assertEquals(['foo', 'bar'], $attributePath->getNames());
    }
}
