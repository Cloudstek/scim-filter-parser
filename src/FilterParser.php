<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser;

use Cloudstek\SCIM\FilterParser\Exception\InvalidValuePathException;
use Cloudstek\SCIM\FilterParser\Exception\TokenizerException;

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
     * @throws TokenizerException|\Nette\Tokenizer\Exception
     *
     * @return AST\Node|null
     */
    public function parse(string $input): ?AST\Node
    {
        $stream = $this->tokenizer->tokenize($input);

        $node = $this->parseInner($stream);


        if ($node !== null && $node instanceof AST\Node === false) {
            throw new TokenizerException('Invalid filter.');
        }

        return $node;
    }

    /**
     * @inheritDoc
     */
    protected function parseValuePath(Tokenizer\Stream $stream, AST\AttributePath $attributePath): AST\ValuePath
    {
        $valuePath = parent::parseValuePath($stream, $attributePath);

        if ($stream->isNext(self::T_SUBATTR)) {
            throw new InvalidValuePathException($stream, 'Invalid value path, unexpected sub attribute.');
        }

        return $valuePath;
    }
}
