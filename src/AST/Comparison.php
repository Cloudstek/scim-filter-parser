<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Comparison.
 */
class Comparison extends AbstractNode
{
    private AttributePath $attributePath;

    private Operator $operator;

    /** @var bool|string|int|float|null */
    private $value;

    /**
     * Comparison.
     *
     * @param AttributePath              $attributePath
     * @param string|Operator            $operator
     * @param bool|float|int|string|null $value
     * @param Node|null                  $parent
     *
     * @throws \UnexpectedValueException On invalid operator.
     */
    public function __construct(AttributePath $attributePath, $operator, $value, ?Node $parent = null)
    {
        parent::__construct($parent);

        $this->attributePath = $attributePath;
        $this->operator = Operator::get($operator);
        $this->value = $value;
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
     * Set attribute path.
     *
     * @param AttributePath $attributePath
     *
     * @return Comparison
     *
     * @internal
     */
    public function setAttributePath(AttributePath $attributePath): Comparison
    {
        $this->attributePath = $attributePath;

        return $this;
    }

    /**
     * Get operator.
     *
     * @return Operator
     */
    public function getOperator(): Operator
    {
        return $this->operator;
    }

    /**
     * Get value.
     *
     * @return bool|float|int|string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
