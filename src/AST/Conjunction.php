<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Logical conjunction (AND).
 */
class Conjunction extends AbstractNode implements Connective
{
    /** @var Node[] */
    private array $nodes;

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
}
