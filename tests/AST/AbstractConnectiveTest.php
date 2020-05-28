<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests\AST;

use Cloudstek\SCIM\FilterParser\AST;
use PHPUnit\Framework\TestCase;

/**
 * Connective test.
 *
 * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractNode
 * @covers \Cloudstek\SCIM\FilterParser\AST\AbstractConnective
 * @covers \Cloudstek\SCIM\FilterParser\AST\AttributePath
 * @covers \Cloudstek\SCIM\FilterParser\AST\Comparison
 */
class AbstractConnectiveTest extends TestCase
{
    public function testCountable()
    {
        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $this->assertCount(2, $conjunction);
    }

    public function testArrayAccess()
    {
        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $this->assertTrue(isset($conjunction[0]));
        $this->assertSame($nodes[0], $conjunction[0]);

        $this->assertTrue(isset($conjunction[1]));
        $this->assertSame($nodes[1], $conjunction[1]);
    }

    public function testArrayAccessSetException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conjunction is read-only.');

        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $conjunction[0] = new AST\Comparison(new AST\AttributePath(null, ['bar']), AST\Operator::EQ(), 'baz');
    }

    public function testArrayAccessUnsetException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conjunction is read-only.');

        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        unset($conjunction[0]);
    }

    public function testArrayAccessWithNonNumericOffset()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected numeric offset.');

        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $conjunction['foo'];
    }

    public function testArrayAccessIssetWithNonNumericOffset()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected numeric offset.');

        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        isset($conjunction['foo']);
    }

    public function testIterator()
    {
        $nodes = [
            new AST\Comparison(new AST\AttributePath(null, ['foo', 'bar']), AST\Operator::EQ(), 'baz'),
            new AST\Comparison(new AST\AttributePath(null, ['baz']), AST\Operator::PR(), null)
        ];

        $conjunction = new AST\Conjunction($nodes, null);

        $foundNodes = [];

        foreach ($conjunction as $node) {
            $foundNodes[] = $node;
        }

        $this->assertEquals($nodes, $foundNodes);
    }
}
