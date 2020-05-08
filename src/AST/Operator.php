<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

use Cloudstek\Enum\Enum;

/**
 * Comparison operator.
 *
 * @method static static EQ()
 * @method static static NE()
 * @method static static CO()
 * @method static static SW()
 * @method static static EW()
 * @method static static GT()
 * @method static static LT()
 * @method static static GE()
 * @method static static LE()
 */
class Operator extends Enum
{
    private const EQ = 'eq';
    private const NE = 'ne';
    private const CO = 'co';
    private const SW = 'sw';
    private const EW = 'ew';
    private const GT = 'gt';
    private const LT = 'lt';
    private const GE = 'ge';
    private const LE = 'le';
}
