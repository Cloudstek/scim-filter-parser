<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Abstract node.
 */
abstract class AbstractNode implements Node
{
    protected ?Node $parent = null;

    /**
     * Abstract node.
     *
     * @param Node|null $parent
     */
    public function __construct(?Node $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?Node
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function setParent(?Node $node): Node
    {
        $this->parent = $node;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasParent($parent = null, bool $recursive = false): bool
    {
        if ($parent === null) {
            return $this->parent !== null;
        }

        $foundParent = false;

        if ($parent instanceof Node) {
            $foundParent = $this->parent === $parent;
        } else {
            $foundParent = isset($this->parent) && get_class($this->parent) === $parent;
        }

        if ($recursive === true && isset($this->parent) && $foundParent === false) {
            return $this->parent->hasParent($parent, true);
        }

        return $foundParent;
    }
}
