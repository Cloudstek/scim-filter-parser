<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Node interface.
 */
interface Node
{
    /**
     * Get parent node.
     *
     * @return Node|null
     */
    public function getParent(): ?Node;

    /**
     * Set parent node.
     *
     * @param Node|null $node
     *
     * @return $this
     */
    public function setParent(?Node $node): self;

    /**
     * Check if node has specific parent.
     *
     * @param string|Node|null $parent FQCN, Node instance to check if parent is of a specific type or instance, or
     *                                 null to return if node has any parent at all.
     * @param bool             $recursive
     *
     * @return bool
     */
    public function hasParent($parent = null, bool $recursive = false): bool;
}
