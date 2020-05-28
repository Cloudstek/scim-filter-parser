<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser;

use Cloudstek\SCIM\FilterParser\Exception\InvalidValuePathException;
use Nette\Tokenizer;

/**
 * SCIM Filter Parser.
 */
class FilterParser extends AbstractParser implements FilterParserInterface
{
    /**
     * SCIM Filter Parser.
     */
    public function __construct()
    {
        parent::__construct(ParserMode::FILTER());
    }

    /**
     * Parse filter string.
     *
     * @param string $input Filter.
     *
     * @throws Tokenizer\Exception
     *
     * @return AST\Node|null
     */
    public function parse(string $input): ?AST\Node
    {
        $stream = $this->tokenizer->tokenize($input);

        return $this->parseInner($stream);
    }
}
