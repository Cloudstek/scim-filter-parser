<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests\AST;

use Cloudstek\SCIM\FilterParser\AST;
use PHPUnit\Framework\TestCase;

/**
 * Node tests.
 *
 * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
 * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
 * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
 * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
 * @covers \Cloudstek\SCIM\FilterParser\AST\Negation
 */
class AbstractNodeTest extends TestCase
{
    public function testHasParent()
    {
        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $negation = new AST\Negation($conjunction);

        $this->assertFalse($negation->hasParent());
        $this->assertTrue($conjunction->hasParent());
        $this->assertTrue($conjunction[0]->hasParent());
        $this->assertTrue($conjunction[1]->hasParent());
    }

    public function testHasParentFqcn()
    {
        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $negation = new AST\Negation($conjunction);

        $this->assertFalse($negation->hasParent(AST\Negation::class));
        $this->assertTrue($conjunction->hasParent(AST\Negation::class));
        $this->assertFalse($conjunction[0]->hasParent(AST\Negation::class));
        $this->assertFalse($conjunction[1]->hasParent(AST\Negation::class));
    }

    public function testHasParentInstance()
    {
        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $negation = new AST\Negation($conjunction);

        $this->assertFalse($negation->hasParent($negation));
        $this->assertTrue($conjunction->hasParent($negation));
        $this->assertFalse($conjunction[0]->hasParent($negation));
        $this->assertFalse($conjunction[1]->hasParent($negation));
    }

    public function testHasParentRecursive()
    {
        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $negation = new AST\Negation($conjunction);

        $this->assertFalse($negation->hasParent());
        $this->assertTrue($conjunction->hasParent(null, true));
        $this->assertTrue($conjunction[0]->hasParent(null, true));
        $this->assertTrue($conjunction[1]->hasParent(null, true));
    }

    public function testHasParentFqcnRecursive()
    {
        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $negation = new AST\Negation($conjunction);

        $this->assertFalse($negation->hasParent(AST\Negation::class, true));
        $this->assertTrue($conjunction->hasParent(AST\Negation::class, true));
        $this->assertTrue($conjunction[0]->hasParent(AST\Negation::class, true));
        $this->assertTrue($conjunction[1]->hasParent(AST\Negation::class, true));
    }
}
