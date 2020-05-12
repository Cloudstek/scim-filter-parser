<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Logical connective interface.
 */
interface Connective extends Node, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * Get nodes.
     *
     * @return Node[]
     */
    public function getNodes(): array;
}
