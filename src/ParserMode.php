<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser;

use Cloudstek\Enum\Enum;

/**
 * Parser mode.
 *
 * @method static static FILTER()
 * @method static static PATH()
 */
class ParserMode extends Enum
{
    private const FILTER = 'filter';
    private const PATH = 'path';
}
