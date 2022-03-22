<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Abstract connective.
 */
abstract class AbstractConnective extends AbstractNode implements Connective
{
    /** @var Node[] */
    protected array $nodes;

    /**
     * Logical conjunction (AND).
     *
     * @param Node[]    $nodes
     * @param Node|null $parent
     */
    public function __construct(array $nodes, ?Node $parent = null)
    {
        parent::__construct($parent);

        $this->nodes = $nodes;

        foreach ($this->nodes as $node) {
            $node->setParent($this);
        }
    }

    /**
     * @inheritDoc
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        if (is_int($offset) === false) {
            throw new \InvalidArgumentException('Expected numeric offset.');
        }

        return isset($this->nodes[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): mixed
    {
        if (is_int($offset) === false) {
            throw new \InvalidArgumentException('Expected numeric offset.');
        }

        return $this->nodes[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        throw new \LogicException('Conjunction is read-only.');
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        throw new \LogicException('Conjunction is read-only.');
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->nodes);
    }
}
