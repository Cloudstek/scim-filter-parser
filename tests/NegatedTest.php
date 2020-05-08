<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests;

use Cloudstek\SCIM\FilterParser\AST;
use Cloudstek\SCIM\FilterParser\FilterParser;
use PHPUnit\Framework\TestCase;

class NegatedTest extends TestCase
{
    private static FilterParser $parser;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$parser = new FilterParser();
    }

    public function testCompareStringValuePathNegated()
    {
        /** @var AST\Negation $node */
        $node = self::$parser->parse('name[not (formatted eq "foobar")]');

        $this->assertInstanceOf(AST\Negation::class, $node);
        $this->assertNull($node->getParent());

        /** @var AST\Comparison $negatedNode */
        $negatedNode = $node->getNode();

        $this->assertInstanceOf(AST\Comparison::class, $negatedNode);
        $this->assertSame($node, $negatedNode->getParent());
        $this->assertEquals(new AST\AttributePath(null, ['name', 'formatted']), $negatedNode->getAttributePath());
        $this->assertSame((string)AST\Operator::EQ(), (string)$negatedNode->getOperator());
        $this->assertSame('foobar', $negatedNode->getValue());
    }

    public function testCompareStringValuePathNegatedConjunction()
    {
        /** @var AST\Negation $node */
        $node = self::$parser->parse('name[not (formatted eq "foobar" and family eq "bar")]');

        $this->assertInstanceOf(AST\Negation::class, $node);
        $this->assertNull($node->getParent());

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
        $this->assertSame((string)AST\Operator::EQ(), (string)$subNode->getOperator());
        $this->assertSame('foobar', $subNode->getValue());
    }
}
