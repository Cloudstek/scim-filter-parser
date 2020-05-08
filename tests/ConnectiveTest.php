<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests;

use Cloudstek\SCIM\FilterParser\AST;
use Cloudstek\SCIM\FilterParser\FilterParser;
use PHPUnit\Framework\TestCase;

class ConnectiveTest extends TestCase
{
    private static FilterParser $parser;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$parser = new FilterParser();
    }

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
        $this->assertSame((string)AST\Operator::EQ(), (string)$nodes[0]->getOperator());
        $this->assertSame('foobar', $nodes[0]->getValue());

        // Comparison (right) numActivations ge 3
        $this->assertInstanceOf(AST\Comparison::class, $nodes[1]);
        $this->assertSame($conjunction, $nodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $nodes[1]->getAttributePath());
        $this->assertSame((string)AST\Operator::GE(), (string)$nodes[1]->getOperator());
        $this->assertSame(3, $nodes[1]->getValue());
    }

    public function testValuePathConjunction()
    {
        /** @var AST\Conjunction $conjunction */
        $conjunction = self::$parser->parse('name[formatted eq "foobar" and length ge 3]');

        $this->assertInstanceOf(AST\Conjunction::class, $conjunction);
        $this->assertNull($conjunction->getParent());

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
        $this->assertSame((string)AST\Operator::EQ(), (string)$nodes[0]->getOperator());
        $this->assertSame('foobar', $nodes[0]->getValue());

        // Comparison (right) numActivations ge 3
        $this->assertInstanceOf(AST\Comparison::class, $nodes[1]);
        $this->assertSame($disjunction, $nodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $nodes[1]->getAttributePath());
        $this->assertSame((string)AST\Operator::GE(), (string)$nodes[1]->getOperator());
        $this->assertSame(3, $nodes[1]->getValue());
    }

    public function testValuePathDisjunction()
    {
        /** @var AST\Disjunction $disjunction */
        $disjunction = self::$parser->parse('name[formatted eq "foobar" or length ge 3]');

        $this->assertInstanceOf(AST\Disjunction::class, $disjunction);
        $this->assertNull($disjunction->getParent());

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
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[0]->getOperator());
        $this->assertSame('foobar', $conjunctionNodes[0]->getValue());

        // Grouped conjunction omparison (right) numActivation ge 3
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($disjunctionNodes[0], $conjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['numActivations']), $conjunctionNodes[1]->getAttributePath());
        $this->assertSame((string)AST\Operator::GE(), (string)$conjunctionNodes[1]->getOperator());
        $this->assertSame(3, $conjunctionNodes[1]->getValue());

        // Ungrouped disjunction comparison (right) userName eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $disjunctionNodes[1]);
        $this->assertSame($disjunction, $disjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['userName']), $disjunctionNodes[1]->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$disjunctionNodes[1]->getOperator());
        $this->assertSame('bar', $disjunctionNodes[1]->getValue());
    }

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
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[0]->getOperator());
        $this->assertSame('foo', $conjunctionNodes[0]->getValue());

        // Comparison (middle) name.middle eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($conjunction, $conjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'middle']), $conjunctionNodes[1]->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[1]->getOperator());
        $this->assertSame('bar', $conjunctionNodes[1]->getValue());

        // Comparison (right) name.last eq "baz"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[2]);
        $this->assertSame($conjunction, $conjunctionNodes[2]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'last']), $conjunctionNodes[2]->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[2]->getOperator());
        $this->assertSame('baz', $conjunctionNodes[2]->getValue());
    }

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
        $this->assertSame((string)AST\Operator::EQ(), (string)$leftConjunctionNodes[0]->getOperator());
        $this->assertSame('foo', $leftConjunctionNodes[0]->getValue());

        // Grouped conjunction comparison (right) name.middle eq "bar"
        $this->assertInstanceOf(AST\Comparison::class, $leftConjunctionNodes[1]);
        $this->assertSame($leftConjunction, $leftConjunctionNodes[1]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'middle']),
            $leftConjunctionNodes[1]->getAttributePath()
        );
        $this->assertSame((string)AST\Operator::EQ(), (string)$leftConjunctionNodes[1]->getOperator());
        $this->assertSame('bar', $leftConjunctionNodes[1]->getValue());

        // Ungrouped conjunction comparison (right) name.last eq "baz"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($conjunction, $conjunctionNodes[1]->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'last']), $conjunctionNodes[1]->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[1]->getOperator());
        $this->assertSame('baz', $conjunctionNodes[1]->getValue());
    }

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
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[0]->getOperator());
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
        $this->assertSame((string)AST\Operator::EQ(), (string)$disjunctionNodes[0]->getOperator());
        $this->assertSame('bar', $disjunctionNodes[0]->getValue());

        // Disjunction comparison (right) name.last eq "baz"
        $this->assertInstanceOf(AST\Comparison::class, $disjunctionNodes[1]);
        $this->assertSame($disjunction, $disjunctionNodes[1]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'last']),
            $disjunctionNodes[1]->getAttributePath()
        );
        $this->assertSame((string)AST\Operator::EQ(), (string)$disjunctionNodes[1]->getOperator());
        $this->assertSame('baz', $disjunctionNodes[1]->getValue());
    }

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
        $this->assertSame((string)AST\Operator::EQ(), (string)$disjunctionNodes[0]->getOperator());
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
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[0]->getOperator());
        $this->assertSame('bar', $conjunctionNodes[0]->getValue());

        // Conjunction comparison (right) name.last eq "baz"
        $this->assertInstanceOf(AST\Comparison::class, $conjunctionNodes[1]);
        $this->assertSame($conjunction, $conjunctionNodes[1]->getParent());
        $this->assertEquals(
            new AST\AttributePath(null, ['name', 'last']),
            $conjunctionNodes[1]->getAttributePath()
        );
        $this->assertSame((string)AST\Operator::EQ(), (string)$conjunctionNodes[1]->getOperator());
        $this->assertSame('baz', $conjunctionNodes[1]->getValue());
    }
}
