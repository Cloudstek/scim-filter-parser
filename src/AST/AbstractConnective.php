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
    public function offsetExists($offset)
    {
        return isset($this->nodes[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->nodes[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Conjunction is read-only.');
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException('Conjunction is read-only.');
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->nodes);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->nodes);
    }
}
