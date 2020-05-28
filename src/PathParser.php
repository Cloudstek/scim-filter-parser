<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser;

use Cloudstek\SCIM\FilterParser\Exception\InvalidValuePathException;
use Cloudstek\SCIM\FilterParser\Exception\TokenizerException;
use Cloudstek\SCIM\FilterParser\Exception\UnexpectedValueException;
use Nette\Tokenizer;

/**
 * SCIM Path Parser.
 */
class PathParser extends AbstractParser implements PathParserInterface
{
    /**
     * SCIM Path Parser.
     */
    public function __construct()
    {
        parent::__construct(ParserMode::PATH());
    }

    /**
     * Parse path string.
     *
     * @param string $input Attribute path..
     *
     * @throws Tokenizer\Exception
     *
     * @return AST\Path
     */
    public function parse(string $input): AST\Path
    {
        $stream = $this->tokenizer->tokenize($input);

        // Attribute path
        $attributePath = $this->parseAttributePath($stream);

        if ($stream->nextToken() !== null) {
            throw new UnexpectedValueException($stream);
        }

        return $attributePath;
    }

    /**
     * @inheritDoc
     */
    protected function parseInner(Tokenizer\Stream $stream, bool $inValuePath = false): ?AST\Node
    {
        // @codeCoverageIgnoreStart
        if ($inValuePath === false) {
            throw new \LogicException('This method should only be called when parsing a value path.');
        }

        // @codeCoverageIgnoreEnd

        return parent::parseInner($stream, $inValuePath);
    }

    /**
     * @inheritDoc
     */
    protected function parseValuePath(Tokenizer\Stream $stream, AST\AttributePath $attributePath): AST\ValuePath
    {
        $valuePath = parent::parseValuePath($stream, $attributePath);

        // Sub attribute
        if ($stream->isNext()) {
            $subAttr = $stream->consumeToken(self::T_SUBATTR)->value;

            // Strip off the '.' at the start
            $subAttr = ltrim($subAttr, '.');

            // NOTE: T_SUBATTR matches end of string so no need to check if stream is at the end.
            return $valuePath->withSubAttribute($subAttr);
        }

        return $valuePath;
    }
}
