<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests;

use Cloudstek\SCIM\FilterParser\AST;
use Cloudstek\SCIM\FilterParser\FilterParser;
use Cloudstek\SCIM\FilterParser\FilterParserInterface;
use Nette\Tokenizer;
use PHPUnit\Framework\TestCase;

/**
 * Filter parser tests.
 *
 * @covers \Cloudstek\SCIM\FilterParser\AbstractParser
 * @covers \Cloudstek\SCIM\FilterParser\FilterParser
 */
class FilterParserTest extends TestCase
{
    private static FilterParser $parser;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$parser = new FilterParser();
    }

    public function testInstantiate()
    {
        $parser = new FilterParser();

        $this->assertInstanceOf(FilterParserInterface::class, $parser);
        $this->assertInstanceOf(FilterParser::class, $parser);
    }

    public function testEmptyParenthesesReturnsNull()
    {
        $node = self::$parser->parse('()');

        $this->assertNull($node);
    }

    public function testEmptyParenthesesInnerReturnsNull()
    {
        $node = self::$parser->parse('(())');

        $this->assertNull($node);
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     */
    public function testPathThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected end of string');

        self::$parser->parse('foo.bar');
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testUnexpectedTypeAtStartThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected  and  on line 1, column 1.');

        self::$parser->parse(' and userName eq "foobar"');
    }

    //region Negation

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
        $this->assertSame(AST\Operator::EQ, $negatedNode->getOperator());
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
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[0]->getOperator());
        $this->assertSame('foobar', $conjunctionNodes[0]->getValue());

        // Conjunction (right) family eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($negatedNode, $conjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'family']), $conjunctionNodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[1]->getOperator());
        $this->assertSame('bar', $conjunctionNodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     * @covers \Cloudstek\SCIM\FilterParser\AST\Negation
     */
    public function testCompareStringNegated()
    {
        /** @var AST\Negation $node */
        $node = self::$parser->parse('not (userName eq "foobar")');

        $this->assertInstanceOf(AST\Negation::class, $node);
        $this->assertNull($node->getParent());

        /** @var AST\Comparison $subNode */
        $subNode = $node->getNode();

        $this->assertInstanceOf(AST\Comparison::class, $subNode);
        $this->assertSame($node, $subNode->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['userName']), $subNode->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $subNode->getOperator());
        $this->assertSame('foobar', $subNode->getValue());
    }

    public function testEmptyNegationReturnsNull()
    {
        $node = self::$parser->parse('not ()');

        $this->assertNull($node);
    }

    public function testEmptyNegationInnerReturnsNull()
    {
        $node = self::$parser->parse('not (())');

        $this->assertNull($node);
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     */
    public function testNegationWithPathThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected end of string');

        self::$parser->parse('not (foo.bar)');
    }
    //endregion

    //region Connectives

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testConjunction()
    {
        /** @var AST\Conjunction $conjunction */
        $conjunction = self::$parser->parse('userName eq "foobar" and numActivations ge 3');

        $this->assertInstanceOf(AST\Conjunction::class, $conjunction);
        $this->assertNull($conjunction->getParent());

        /** @var AST\Comparison[] $nodes */
        $nodes = $conjunction->getNodes();

        $this->assertCount(2, $nodes);

        // Comparison (left) userName eq "foobar"
        $this->assertInstanceOf(AST\Comparison::class, $nodes[0]);
        $this->assertSame($conjunction, $nodes[0]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['userName']), $nodes[0]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $nodes[0]->getOperator());
        $this->assertSame('foobar', $nodes[0]->getValue());

        // Comparison (right) numActivations ge 3
        $this->assertInstanceOf(AST\Comparison::class, $nodes[1]);
        $this->assertSame($conjunction, $nodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $nodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::GE, $nodes[1]->getOperator());
        $this->assertSame(3, $nodes[1]->getValue());
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
        $this->assertSame(AST\Operator::EQ, $nodes[0]->getOperator());
        $this->assertSame('foobar', $nodes[0]->getValue());

        // Comparison (right) length ge 3
        $this->assertInstanceOf(AST\Comparison::class, $nodes[1]);
        $this->assertSame($conjunction, $nodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'length']), $nodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::GE, $nodes[1]->getOperator());
        $this->assertSame(3, $nodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testDisjunction()
    {
        /** @var AST\Disjunction $disjunction */
        $disjunction = self::$parser->parse('userName eq "foobar" or numActivations ge 3');

        $this->assertInstanceOf(AST\Disjunction::class, $disjunction);
        $this->assertNull($disjunction->getParent());

        /** @var AST\Comparison[] $nodes */
        $nodes = $disjunction->getNodes();

        $this->assertCount(2, $nodes);

        // Comparison (left) username eq "foobar"
        $this->assertInstanceOf(AST\Comparison::class, $nodes[0]);
        $this->assertSame($disjunction, $nodes[0]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['userName']), $nodes[0]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $nodes[0]->getOperator());
        $this->assertSame('foobar', $nodes[0]->getValue());

        // Comparison (right) numActivations ge 3
        $this->assertInstanceOf(AST\Comparison::class, $nodes[1]);
        $this->assertSame($disjunction, $nodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $nodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::GE, $nodes[1]->getOperator());
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
        $this->assertSame(AST\Operator::EQ, $nodes[0]->getOperator());
        $this->assertSame('foobar', $nodes[0]->getValue());

        // Comparison (right) length ge 3
        $this->assertInstanceOf(AST\Comparison::class, $nodes[1]);
        $this->assertSame($disjunction, $nodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'length']), $nodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::GE, $nodes[1]->getOperator());
        $this->assertSame(3, $nodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testGroupedConjunctionDisjunction()
    {
        /** @var AST\Disjunction $node */
        $disjunction = self::$parser->parse('(userName eq "foobar" and numActivations ge 3) or userName eq "bar"');

        $this->assertInstanceOf(AST\Disjunction::class, $disjunction);
        $this->assertNull($disjunction->getParent());

        $disjunctionNodes = $disjunction->getNodes();

        $this->assertCount(2, $disjunctionNodes);

        // Grouped conjunction
        $this->assertInstanceOf(AST\Conjunction::class, $disjunctionNodes[0]);
        $this->assertSame($disjunction, $disjunctionNodes[0]->getParent());

        $conjunctionNodes = $disjunctionNodes[0]->getNodes();

        $this->assertCount(2, $conjunctionNodes);

        // Grouped conjunction omparison (left) userName eq "foobar"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[0]);
        $this->assertSame($disjunctionNodes[0], $conjunctionNodes[0]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['userName']), $conjunctionNodes[0]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[0]->getOperator());
        $this->assertSame('foobar', $conjunctionNodes[0]->getValue());

        // Grouped conjunction omparison (right) numActivation ge 3
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($disjunctionNodes[0], $conjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $conjunctionNodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::GE, $conjunctionNodes[1]->getOperator());
        $this->assertSame(3, $conjunctionNodes[1]->getValue());

        // Ungrouped disjunction comparison (right) userName eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $disjunctionNodes[1]);
        $this->assertSame($disjunction, $disjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['userName']), $disjunctionNodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $disjunctionNodes[1]->getOperator());
        $this->assertSame('bar', $disjunctionNodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testUngroupedDoubleConjunction()
    {
        /** @var AST\Conjunction $conjunction */
        $conjunction = self::$parser->parse('name.first eq "foo" and name.middle eq "bar" and name.last eq "baz"');

        $this->assertInstanceOf(AST\Conjunction::class, $conjunction);
        $this->assertNull($conjunction->getParent());

        /** @var AST\Comparison[] $conjunctionNodes */
        $conjunctionNodes = $conjunction->getNodes();

        $this->assertCount(3, $conjunctionNodes);

        // Comparison (left) name.first eq "foo"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[0]);
        $this->assertSame($conjunction, $conjunctionNodes[0]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'first']), $conjunctionNodes[0]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[0]->getOperator());
        $this->assertSame('foo', $conjunctionNodes[0]->getValue());

        // Comparison (middle) name.middle eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($conjunction, $conjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'middle']), $conjunctionNodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[1]->getOperator());
        $this->assertSame('bar', $conjunctionNodes[1]->getValue());

        // Comparison (right) name.last eq "baz"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[2]);
        $this->assertSame($conjunction, $conjunctionNodes[2]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'last']), $conjunctionNodes[2]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[2]->getOperator());
        $this->assertSame('baz', $conjunctionNodes[2]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testGroupedDoubleConjunction()
    {
        /** @var AST\Conjunction $conjunction */
        $conjunction = self::$parser->parse('(name.first eq "foo" and name.middle eq "bar") and name.last eq "baz"');

        $this->assertInstanceOf(AST\Conjunction::class, $conjunction);
        $this->assertNull($conjunction->getParent());

        // Ungrouped conjunction
        $conjunctionNodes = $conjunction->getNodes();

        $this->assertCount(2, $conjunctionNodes);

        // Grouped conjunction
        $leftConjunction = $conjunctionNodes[0];

        $this->assertInstanceOf(AST\Conjunction::class, $leftConjunction);
        $this->assertSame($conjunction, $leftConjunction->getParent());

        $leftConjunctionNodes = $leftConjunction->getNodes();

        $this->assertCount(2, $leftConjunctionNodes);

        // Grouped conjunction comparison (left) name.first eq "foo"
        $this->assertInstanceOf(AST\Comparison::class, $leftConjunctionNodes[0]);
        $this->assertSame($leftConjunction, $leftConjunctionNodes[0]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'first']),
            $leftConjunctionNodes[0]->getAttributePath()
        );
        $this->assertSame(AST\Operator::EQ, $leftConjunctionNodes[0]->getOperator());
        $this->assertSame('foo', $leftConjunctionNodes[0]->getValue());

        // Grouped conjunction comparison (right) name.middle eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $leftConjunctionNodes[1]);
        $this->assertSame($leftConjunction, $leftConjunctionNodes[1]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'middle']),
            $leftConjunctionNodes[1]->getAttributePath()
        );
        $this->assertSame(AST\Operator::EQ, $leftConjunctionNodes[1]->getOperator());
        $this->assertSame('bar', $leftConjunctionNodes[1]->getValue());

        // Ungrouped conjunction comparison (right) name.last eq "baz"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($conjunction, $conjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'last']), $conjunctionNodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[1]->getOperator());
        $this->assertSame('baz', $conjunctionNodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testUngroupedConjunctionDisjunction()
    {
        /** @var AST\Conjunction $conjunction */
        $conjunction = self::$parser->parse('name.first eq "foo" and name.middle eq "bar" or name.last eq "baz"');

        $this->assertInstanceOf(AST\Conjunction::class, $conjunction);
        $this->assertNull($conjunction->getParent());

        // Conjunction
        $conjunctionNodes = $conjunction->getNodes();

        $this->assertCount(2, $conjunctionNodes);

        // Conjunction comparison (left) name.first eq "foo"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[0]);
        $this->assertSame($conjunction, $conjunctionNodes[0]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'first']), $conjunctionNodes[0]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[0]->getOperator());
        $this->assertSame('foo', $conjunctionNodes[0]->getValue());

        // Conjunction disjunction (right)
        /** @var AST\Disjunction $disjunction */
        $disjunction = $conjunctionNodes[1];

        $this->assertInstanceOf(AST\Disjunction::class, $disjunction);
        $this->assertSame($conjunction, $disjunction->getParent());

        $disjunctionNodes = $disjunction->getNodes();

        $this->assertCount(2, $disjunctionNodes);

        // Disjunction comparison (left) name.middle eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $disjunctionNodes[0]);
        $this->assertSame($disjunction, $disjunctionNodes[0]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'middle']),
            $disjunctionNodes[0]->getAttributePath()
        );
        $this->assertSame(AST\Operator::EQ, $disjunctionNodes[0]->getOperator());
        $this->assertSame('bar', $disjunctionNodes[0]->getValue());

        // Disjunction comparison (right) name.last eq "baz"
        $this->assertInstanceOf(AST\Comparison::class, $disjunctionNodes[1]);
        $this->assertSame($disjunction, $disjunctionNodes[1]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'last']),
            $disjunctionNodes[1]->getAttributePath()
        );
        $this->assertSame(AST\Operator::EQ, $disjunctionNodes[1]->getOperator());
        $this->assertSame('baz', $disjunctionNodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testUngroupedDisjunctionConjunction()
    {
        /** @var AST\Disjunction $disjunction */
        $disjunction = self::$parser->parse('name.first eq "foo" or name.middle eq "bar" and name.last eq "baz"');

        $this->assertInstanceOf(AST\Disjunction::class, $disjunction);
        $this->assertNull($disjunction->getParent());

        // Disjunction
        $disjunctionNodes = $disjunction->getNodes();

        $this->assertCount(2, $disjunctionNodes);

        // Disjunction comparison (left) name.first eq "foo"
        $this->assertInstanceOf(AST\Comparison::class, $disjunctionNodes[0]);
        $this->assertSame($disjunction, $disjunctionNodes[0]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'first']), $disjunctionNodes[0]->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $disjunctionNodes[0]->getOperator());
        $this->assertSame('foo', $disjunctionNodes[0]->getValue());

        // Disjunction conjunction (right)
        /** @var AST\Conjunction $conjunction */
        $conjunction = $disjunctionNodes[1];

        $this->assertInstanceOf(AST\Conjunction::class, $conjunction);
        $this->assertSame($disjunction, $conjunction->getParent());

        $conjunctionNodes = $conjunction->getNodes();

        $this->assertCount(2, $conjunctionNodes);

        // Conjunction comparison (left) name.middle eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[0]);
        $this->assertSame($conjunction, $conjunctionNodes[0]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'middle']),
            $conjunctionNodes[0]->getAttributePath()
        );
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[0]->getOperator());
        $this->assertSame('bar', $conjunctionNodes[0]->getValue());

        // Conjunction comparison (right) name.last eq "baz"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($conjunction, $conjunctionNodes[1]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'last']),
            $conjunctionNodes[1]->getAttributePath()
        );
        $this->assertSame(AST\Operator::EQ, $conjunctionNodes[1]->getOperator());
        $this->assertSame('baz', $conjunctionNodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testConjunctionWithPresentComparison()
    {
        /** @var AST\Conjunction $conjunction */
        $conjunction = self::$parser->parse('userName pr and numActivations ge 3');

        $this->assertInstanceOf(AST\Conjunction::class, $conjunction);
        $this->assertNull($conjunction->getParent());

        /** @var AST\Comparison[] $nodes */
        $nodes = $conjunction->getNodes();

        $this->assertCount(2, $nodes);

        // Comparison (left) userName pr
        $this->assertInstanceOf(AST\Comparison::class, $nodes[0]);
        $this->assertSame($conjunction, $nodes[0]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['userName']), $nodes[0]->getAttributePath());
        $this->assertSame(AST\Operator::PR, $nodes[0]->getOperator());
        $this->assertNull($nodes[0]->getValue());

        // Comparison (right) numActivations ge 3
        $this->assertInstanceOf(AST\Comparison::class, $nodes[1]);
        $this->assertSame($conjunction, $nodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $nodes[1]->getAttributePath());
        $this->assertSame(AST\Operator::GE, $nodes[1]->getOperator());
        $this->assertSame(3, $nodes[1]->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     * @covers \Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException
     */
    public function testConnectiveWithEmptyRightSideThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected ( on line 1, column 17.');

        self::$parser->parse('userName pr and ()');
    }
    //endregion

    //region Comparisons
    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testCompareString()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('userName eq "foobar"');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['userName']), $node->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testCompareStringSubAttribute()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('name.formatted eq "foobar"');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'formatted']), $node->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

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
        $this->assertSame(AST\Operator::EQ, $node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\ValuePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testCompareStringDollarValuePath()
    {
        /** @var AST\ValuePath $valuePath */
        $valuePath = self::$parser->parse('members[$ref eq "https://example.com/v2/Users/71ddacd2-a8e7-49b8-a5db-ae50d0a5bfd7"]');

        $this->assertInstanceOf(AST\ValuePath::class, $valuePath);
        $this->assertNull($valuePath->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['members']), $valuePath->getAttributePath());

        /** @var AST\Comparison $node */
        $node = $valuePath->getNode();

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertSame($valuePath, $node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['members', '$ref']), $node->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $node->getOperator());
        $this->assertSame('https://example.com/v2/Users/71ddacd2-a8e7-49b8-a5db-ae50d0a5bfd7', $node->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
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
        $this->assertSame(AST\Operator::EQ, $node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
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
        $this->assertSame(AST\Operator::EQ, $node->getOperator());
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
        $this->assertSame(AST\Operator::EQ, $node->getOperator());
        $this->assertSame('foobar', $node->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testCompareBool()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('activated eq true');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['activated']), $node->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $node->getOperator());
        $this->assertSame(true, $node->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testCompareInt()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('numActivations ge 3');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $node->getAttributePath());
        $this->assertSame(AST\Operator::GE, $node->getOperator());
        $this->assertSame(3, $node->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testCompareFloat()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('numActivations lt 4.5');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $node->getAttributePath());
        $this->assertSame(AST\Operator::LT, $node->getOperator());
        $this->assertEqualsWithDelta(4.5, $node->getValue(), 0.0001);
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testCompareNull()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('numActivations eq null');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $node->getAttributePath());
        $this->assertSame(AST\Operator::EQ, $node->getOperator());
        $this->assertNull($node->getValue());
    }

    /**
     * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
     * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
     * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
     */
    public function testComparePresent()
    {
        /** @var AST\Comparison $node */
        $node = self::$parser->parse('numActivations pr');

        $this->assertInstanceOf(AST\Comparison::class, $node);
        $this->assertNull($node->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $node->getAttributePath());
        $this->assertSame(AST\Operator::PR, $node->getOperator());
        $this->assertNull($node->getValue());
    }
    //endregion

    //region Value paths
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
    public function testValuePathWithSubAttributeThrowsException()
    {
        $this->expectException(Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected \'.baz\' on line 1, column 19.');

        self::$parser->parse('name[foo eq "bar"].baz');
    }
    //endregion
}
