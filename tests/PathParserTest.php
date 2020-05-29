<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests;

use Cloudstek\SCIM\FilterParser\AST;
use Cloudstek\SCIM\FilterParser\Exception\InvalidValuePathException;
use Cloudstek\SCIM\FilterParser\PathParser;
use Cloudstek\SCIM\FilterParser\PathParserInterface;
use Nette\Tokenizer;
use PHPUnit\Framework\TestCase;

/**
 * Path parser tests.
 *
 * @covers \Cloudstek\SCIM\FilterParser\AbstractParser
 * @covers \Cloudstek\SCIM\FilterParser\PathParser
 */
class PathParserTest extends TestCase
{
    private static PathParser $parser;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$parser = new PathParser();
    }

    public function testInstantiate()
    {
        $parser = new PathParser();

        $this->assertInstanceOf(PathParserInterface::class, $parser);
        $this->assertInstanceOf(PathParser::class, $parser);
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\ValuePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testNegationAtStartThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage(
            "Unexpected 'not ' on line 1, column 1."
        );

        self::$parser->parse('not ()');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testParenthesesAtStartThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage(
            "Unexpected '(' on line 1, column 1."
        );

        self::$parser->parse('()');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testUnexpectedTypeAtStartThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage(
            "Unexpected ' and ' on line 1, column 1."
        );

        self::$parser->parse(' and userName eq "foobar"');
    }

    //region Negations

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\ValuePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     * @covers \Cloudstek\SCIM\FilterParser\AST\Negation
     */
    public function testCompareStringValuePathNegated()
    {
        /** @var AST\ValuePath $valuePath */
        $valuePath = self::$parser->parse('name[not (formatted eq "foobar")]');

        $this->assertInstanceOf(AST\ValuePath::class, $valuePath);
        $this->assertNull($valuePath->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name']),
            $valuePath->getAttributePath()
        );

        /** @var AST\Negation $node */
        $node = $valuePath->getNode();

        $this->assertInstanceOf(AST\Negation::class, $node);
        $this->assertSame($valuePath, $node->getParent());

        /** @var AST\Comparison $negatedNode */
        $negatedNode = $node->getNode();

        $this->assertInstanceOf(AST\Comparison::class, $negatedNode);
        $this->assertSame($node, $negatedNode->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'formatted']), $negatedNode->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$negatedNode->getOperator());
        $this->assertSame('foobar', $negatedNode->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\ValuePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     * @covers \Cloudstek\SCIM\FilterParser\AST\Negation
     */
    public function testCompareStringValuePathNegatedConjunction()
    {
        /** @var AST\ValuePath $valuePath */
        $valuePath = self::$parser->parse('name[not (formatted eq "foobar" and family eq "bar")]');

        $this->assertInstanceOf(AST\ValuePath::class, $valuePath);
        $this->assertNull($valuePath->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name']),
            $valuePath->getAttributePath()
        );

        /** @var AST\Negation $node */
        $node = $valuePath->getNode();

        $this->assertInstanceOf(AST\Negation::class, $node);
        $this->assertSame($valuePath, $node->getParent());

        /** @var AST\Conjunction $negatedNode */
        $negatedNode = $node->getNode();

        $this->assertInstanceOf(AST\Conjunction::class, $negatedNode);
        $this->assertSame($node, $negatedNode->getParent());

        /** @var AST\Comparison[] $conjunctionNodes */
        $conjunctionNodes = $negatedNode->getNodes();

        $this->assertCount(2, $conjunctionNodes);

        // Conjunction (left) formatted eq "foobar"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[0]);
        $this->assertSame($negatedNode, $conjunctionNodes[0]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'formatted']),
            $conjunctionNodes[0]->getAttributePath()
        );
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[0]->getOperator());
        $this->assertSame('foobar', $conjunctionNodes[0]->getValue());

        // Conjunction (right) family eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($negatedNode, $conjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'family']), $conjunctionNodes[1]->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[1]->getOperator());
        $this->assertSame('bar', $conjunctionNodes[1]->getValue());
    }
    //endregion

    //region Connectives
    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testConjunctionThrowsException()
    {
        $this->expectException(\Nette\Tokenizer\Exception::class);
        $this->expectExceptionMessage(
            'Unexpected  eq  on line 1, column 9.'
        );

        self::$parser->parse('userName eq "foobar" and numActivations ge 3');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testDisjunctionThrowsException()
    {
        $this->expectException(\Nette\Tokenizer\Exception::class);
        $this->expectExceptionMessage(
            'Unexpected  eq  on line 1, column 9.'
        );

        self::$parser->parse('userName eq "foobar" or numActivations ge 3');
    }
    //endregion

    //region Comparisons
    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testComparisonThrowsException()
    {
        $this->expectException(\Nette\Tokenizer\Exception::class);
        $this->expectExceptionMessage(
            'Unexpected  eq  on line 1, column 9.'
        );

        self::$parser->parse('userName eq "foobar"');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     *
     */
    public function testSubAttributeComparisonThrowsException()
    {
        $this->expectException(\Nette\Tokenizer\Exception::class);
        $this->expectExceptionMessage(
            'Unexpected  eq  on line 1, column 15.'
        );

        self::$parser->parse('name.formatted eq "foobar"');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testAttributeWithSchemeComparisonThrowsException()
    {
        $this->expectException(\Nette\Tokenizer\Exception::class);
        $this->expectExceptionMessage(
            'Unexpected  eq  on line 1, column 68.'
        );

        self::$parser->parse('urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:userName eq "foobar"');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testSubAttributeWithSchemeComparisonThrowsException()
    {
        $this->expectException(\Nette\Tokenizer\Exception::class);
        $this->expectExceptionMessage(
            'Unexpected  eq  on line 1, column 74.'
        );

        self::$parser->parse(
            'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:name.formatted eq "foobar"'
        );
    }
    //endregion

    //region Value paths
    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\ValuePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
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

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\ValuePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
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

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\ValuePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testValuePathConjunction()
    {
        /** @var AST\ValuePath $valuePath */
        $valuePath = self::$parser->parse('name[formatted eq "foobar" and length ge 3]');

        $this->assertInstanceOf(AST\ValuePath::class, $valuePath);
        $this->assertNull($valuePath->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name']),
            $valuePath->getAttributePath()
        );

        /** @var AST\Conjunction $conjunction */
        $conjunction = $valuePath->getNode();

        $this->assertInstanceOf(AST\Conjunction::class, $conjunction);
        $this->assertSame($valuePath, $conjunction->getParent());

        /** @var AST\Comparison[] $nodes */
        $nodes = $conjunction->getNodes();

        $this->assertCount(2, $nodes);

        // Comparison (left) formatted eq "foobar"
        $this->assertInstanceOf(AST\Comparison::class, $nodes[0]);
        $this->assertSame($conjunction, $nodes[0]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'formatted']), $nodes[0]->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$nodes[0]->getOperator());
        $this->assertSame('foobar', $nodes[0]->getValue());

        // Comparison (right) length ge 3
        $this->assertInstanceOf(AST\Comparison::class, $nodes[1]);
        $this->assertSame($conjunction, $nodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'length']), $nodes[1]->getAttributePath());
        $this->assertSame((string)AST\Operator::GE(), (string)$nodes[1]->getOperator());
        $this->assertSame(3, $nodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\ValuePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testValuePathDisjunction()
    {
        /** @var AST\ValuePath $valuePath */
        $valuePath = self::$parser->parse('name[formatted eq "foobar" or length ge 3]');

        $this->assertInstanceOf(AST\ValuePath::class, $valuePath);
        $this->assertNull($valuePath->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name']),
            $valuePath->getAttributePath()
        );

        /** @var AST\Disjunction $disjunction */
        $disjunction = $valuePath->getNode();

        $this->assertInstanceOf(AST\Disjunction::class, $disjunction);
        $this->assertSame($valuePath, $disjunction->getParent());

        /** @var AST\Comparison[] $nodes */
        $nodes = $disjunction->getNodes();

        $this->assertCount(2, $nodes);

        // Comparison (left) formatted eq "foobar"
        $this->assertInstanceOf(AST\Comparison::class, $nodes[0]);
        $this->assertSame($disjunction, $nodes[0]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'formatted']), $nodes[0]->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$nodes[0]->getOperator());
        $this->assertSame('foobar', $nodes[0]->getValue());

        // Comparison (right) length ge 3
        $this->assertInstanceOf(AST\Comparison::class, $nodes[1]);
        $this->assertSame($disjunction, $nodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'length']), $nodes[1]->getAttributePath());
        $this->assertSame((string)AST\Operator::GE(), (string)$nodes[1]->getOperator());
        $this->assertSame(3, $nodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testValuePathNestedThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected formatted on line 1, column 6.');

        self::$parser->parse('name[formatted[foo eq "bar"]]');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testValuePathComplexNestedThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected formatted on line 1, column 18.');

        self::$parser->parse('name[not (foo eq "bar" and formatted[foo eq "baz"])]');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testValuePathEmptyThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected [ on line 1, column 5.');

        self::$parser->parse('name[]');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\ValuePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testValuePathWithSubAttribute()
    {
        /** @var AST\ValuePath $node */
        $node = self::$parser->parse('name[foo eq "bar"].baz');

        $this->assertInstanceOf(AST\ValuePath::class, $node);
        $this->assertNull($node->getParent());

        $this->assertEquals(new AST\AttributePath(null, ['name', 'baz']), $node->getAttributePath());

        /** @var AST\Comparison $comparison */
        $comparison = $node->getNode();

        $this->assertInstanceOf(AST\Comparison::class, $comparison);
        $this->assertEquals(new AST\AttributePath(null, ['name', 'foo']), $comparison->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$comparison->getOperator());
        $this->assertSame('bar', $comparison->getValue());
    }

    public function testValuePathWithSubAttributeShouldBeEndOfPath()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage("Unexpected '.baz.fooba' on line 1, column 19.");

        self::$parser->parse('name[foo eq "bar"].baz.foobar');
    }

    //endregion

    //region Attribute paths
    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     */
    public function testAttributePath()
    {
        /** @var AST\AttributePath $node */
        $node = self::$parser->parse('name');

        $this->assertInstanceOf(AST\AttributePath::class, $node);
        $this->assertCount(1, $node);
        $this->assertSame('name', $node[0]);
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     */
    public function testAttributePathWithSubAttribute()
    {
        /** @var AST\AttributePath $node */
        $node = self::$parser->parse('name.last');

        $this->assertInstanceOf(AST\AttributePath::class, $node);
        $this->assertCount(2, $node);
        $this->assertSame('name', $node[0]);
        $this->assertSame('last', $node[1]);
    }
    //endregion
}
