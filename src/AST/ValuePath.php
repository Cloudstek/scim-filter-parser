<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Value path.
 */
class ValuePath extends AbstractNode implements Path
{
    private AttributePath $attributePath;

    private ?string $subAttribute;

    private Node $node;

    /**
     * Value path..
     *
     * @param AttributePath $attributePath
     * @param Node          $node
     * @param string|null   $subAttribute
     * @param Node|null     $parent
     */
    public function __construct(
        AttributePath $attributePath,
        Node $node,
        ?string $subAttribute = null,
        ?Node $parent = null
    ) {
        parent::__construct($parent);

        $this->attributePath = $attributePath;
        $this->subAttribute = $subAttribute;

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
     * Get sub attribute.
     *
     * Paths in PATCH requests can have a value path followed by a sub attribute.
     *
     * @return string|null
     */
    public function getSubAttribute(): ?string
    {
        return $this->subAttribute;
    }

    /**
     * Return a new ValuePath with sub attribute.
     *
     * @param string $subAttribute
     *
     * @return ValuePath
     */
    public function withSubAttribute(string $subAttribute): ValuePath
    {
        $self = clone($this);
        $self->subAttribute = $subAttribute;

        return $self;
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
