<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Attribute path.
 */
class AttributePath implements \ArrayAccess, \IteratorAggregate, \Countable, Path
{
    private ?string $schema;

    /** @var string[] */
    private array $names;

    /**
     * Attribute path.
     *
     * @param string   $schema
     * @param string[] $names
     *
     * @throws \InvalidArgumentException On empty array of names.
     */
    public function __construct(?string $schema, array $names)
    {
        if (empty($names)) {
            throw new \InvalidArgumentException('Attribute path should contain at least one attribute name.');
        }

        $this->schema = $schema;
        $this->names = $names;
    }

    /**
     * Get schema URI.
     *
     * @return string|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * Get names.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * Get attribute names as dotted path notation.
     *
     * @return string
     */
    public function getPath(): string
    {
        return implode('.', $this->names);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        if (is_int($offset) === false) {
            throw new \InvalidArgumentException('Expected numeric offset.');
        }

        return isset($this->names[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (is_int($offset) === false) {
            throw new \InvalidArgumentException('Expected numeric offset.');
        }

        return $this->names[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Attribute path is read-only.');
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException('Attribute path is read-only.');
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->names);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->names);
    }

    public function __toString()
    {
        return $this->getPath();
    }
}
