<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests;

use Cloudstek\SCIM\FilterParser\AST;
use Cloudstek\SCIM\FilterParser\FilterParser;
use PHPUnit\Framework\TestCase;

class ComparisonTest extends TestCase
{
    private static FilterParser $parser;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$parser = new FilterParser();
    }

    public function testCompareString()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('userName eq "foobar"');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['userName']), $node->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    public function testCompareStringSubAttribute()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('name.formatted eq "foobar"');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'formatted']), $node->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    public function testCompareStringValuePath()
    {
        /** @var AST\ValuePath $valuePath */
        $valuePath = self::$parser->parse('name[formatted eq "foobar"]');

        $this->assertInstanceOf(AST\ValuePath::class, $valuePath);
        $this->assertNull($valuePath->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name']), $valuePath->getAttributePath());

        /** @var AST\Comparison $node */
        $node = $valuePath->getNode();

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertSame($valuePath, $node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'formatted']), $node->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    public function testCompareStringScheme()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:userName eq "foobar"');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(
            new AST\AttributePath('urn:ietf:params:scim:schemas:extension:enterprise:2.0:User', ['userName']),
            $node->getAttributePath()
        );
        $this->assertSame((string)AST\Operator::EQ(), (string)$node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    public function testCompareStringSubAttributeScheme()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse(
            'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:name.formatted eq "foobar"'
        );

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(
            new AST\AttributePath('urn:ietf:params:scim:schemas:extension:enterprise:2.0:User', ['name', 'formatted']),
            $node->getAttributePath()
        );
        $this->assertSame((string)AST\Operator::EQ(), (string)$node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    public function testCompareStringSchemeValuePath()
    {
        /** @var AST\ValuePath $valuePath */
        $valuePath = self::$parser->parse(
            'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:name[formatted eq "foobar"]'
        );

        $this->assertInstanceOf(AST\ValuePath::class, $valuePath);
        $this->assertNull($valuePath->getParent());
        $this->assertEquals(
            new AST\AttributePath('urn:ietf:params:scim:schemas:extension:enterprise:2.0:User', ['name']),
            $valuePath->getAttributePath()
        );

        /** @var AST\Comparison $node */
        $node = $valuePath->getNode();

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertSame($valuePath, $node->getParent());
        $this->assertEquals(
            new AST\AttributePath('urn:ietf:params:scim:schemas:extension:enterprise:2.0:User', ['name', 'formatted']),
            $node->getAttributePath()
        );
        $this->assertSame((string)AST\Operator::EQ(), (string)$node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    public function testCompareBool()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('activated eq true');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['activated']), $node->getAttributePath());
        $this->assertSame(AST\Operator::EQ(), $node->getOperator());
        $this->assertSame(true, $node->getValue());
    }

    public function testCompareInt()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('numActivations ge 3');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $node->getAttributePath());
        $this->assertSame(AST\Operator::GE(), $node->getOperator());
        $this->assertSame(3, $node->getValue());
    }

    public function testCompareFloat()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('numActivations lt 4.5');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $node->getAttributePath());
        $this->assertSame(AST\Operator::LT(), $node->getOperator());
        $this->assertEqualsWithDelta(4.5, $node->getValue(), 0.0001);
    }

    public function testCompareNull()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('numActivations eq null');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $node->getAttributePath());
        $this->assertSame(AST\Operator::EQ(), $node->getOperator());
        $this->assertNull($node->getValue());
    }

    public function testComparePresent()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('numActivations pr');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $node->getAttributePath());
        $this->assertSame(AST\Operator::PR(), $node->getOperator());
        $this->assertNull($node->getValue());
    }
}
