<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\AST;

/**
 * Comparison operator.
 */
enum Operator: string
{
    case EQ = 'eq';

    case NE = 'ne';

    case CO = 'co';

    case SW = 'sw';

    case EW = 'ew';

    case GT = 'gt';

    case LT = 'lt';

    case GE = 'ge';

    case LE = 'le';

    case PR = 'pr';
}
