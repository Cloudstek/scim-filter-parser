<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser;

/**
 * SCIM Filter Parser interface.
 */
interface FilterParserInterface
{
    /**
     * Parse filter string.
     *
     * @param string $input SCIM filter.
     *
     * @throws \Nette\Tokenizer\Exception
     *
     * @return AST\Node|null
     */
    public function parse(string $input): ?AST\Node;
}
