<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Attribute path.
 */
class AttributePath
{
    private ?string $schema;

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
}
