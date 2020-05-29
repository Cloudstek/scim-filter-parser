<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser;

use Cloudstek\SCIM\FilterParser\Exception\TokenizerException;
use Nette\Tokenizer;

/**
 * SCIM Path Parser interface.
 *
 * @see https://tools.ietf.org/html/rfc7644#section-3.5.2
 */
interface PathParserInterface
{
    /**
     * Parse path string.
     *
     * @param string $input SCIM attribute path.
     *
     * @throws Tokenizer\Exception
     *
     * @return AST\Path
     */
    public function parse(string $input): AST\Path;
}
