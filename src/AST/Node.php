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
}
