<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Negation (not).
 */
class Negation extends AbstractNode
{
    private Node $node;

    /**
     * Negation.
     *
     * @param Node      $node
     * @param Node|null $parent
     */
    public function __construct(Node $node, ?Node $parent = null)
    {
        parent::__construct($parent);

        $this->node = $node;
        $this->node->setParent($this);
    }

    /**
     * Get node.
     *
     * @return Node
     */
    public function getNode(): Node
    {
        return $this->node;
    }

    /**
     * Set node.
     *
     * @param Node $node
     *
     * @return Negation
     */
    public function setNode(Node $node): Negation
    {
        $this->node = $node;

        return $this;
    }
}
