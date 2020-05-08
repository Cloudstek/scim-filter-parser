<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Node.
 */
abstract class AbstractNode implements Node
{
    protected ?Node $parent = null;

    /**
     * Node.
     *
     * @param Node|null $parent
     */
    public function __construct(?Node $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent node.
     *
     * @return Node|null
     */
    public function getParent(): ?Node
    {
        return $this->parent;
    }

    /**
     * Set parent node.
     *
     * @param Node}null $node
     *
     * @return $this
     */
    public function setParent(?Node $node): self
    {
        $this->parent = $node;

        return $this;
    }
}
