<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser;

/**
 * Parser mode.
 */
enum ParserMode: string
{
    case FILTER = 'filter';

    case PATH = 'path';
}
