<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Value path.
 */
class ValuePath extends AbstractNode
{
    private AttributePath $attributePath;

    private Node $node;

    /**
     * Value path..
     *
     * @param AttributePath $attributePath
     * @param Node          $node
     * @param Node|null     $parent
     */
    public function __construct(AttributePath $attributePath, Node $node, ?Node $parent = null)
    {
        parent::__construct($parent);

        $this->attributePath = $attributePath;
        $this->node = $node;

        $this->node->setParent($this);
    }

    /**
     * Get attribute path.
     *
     * @return AttributePath
     */
    public function getAttributePath(): AttributePath
    {
        return $this->attributePath;
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
}
