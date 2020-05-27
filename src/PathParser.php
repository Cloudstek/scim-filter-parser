<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser;

use Cloudstek\SCIM\FilterParser\Exception\InvalidPatchPathException;
use Cloudstek\SCIM\FilterParser\Exception\InvalidValuePathException;
use Cloudstek\SCIM\FilterParser\Exception\TokenizerException;

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
     * @throws TokenizerException|\Nette\Tokenizer\Exception
     *
     * @return AST\Path
     */
    public function parse(string $input): AST\Path
    {
        $stream = $this->tokenizer->tokenize($input);

        // Expect attribute or value path.
        if ($stream->isNext(self::T_NAME) === false) {
            throw new TokenizerException(
                sprintf(
                    'Expected an attribute or value path, got "%s".',
                    $stream->nextValue()
                ),
                $stream
            );
        }

        // Get attribute name
        $name = $stream->matchNext(self::T_NAME)->value;

        // Attribute scheme
        $scheme = null;

        if (strpos($name, ':') !== false) {
            $lastColonPos = strrpos($name, ':');
            $scheme = substr($name, 0, $lastColonPos);
            $name = substr($name, $lastColonPos + 1);
        }

        // Attribute path
        $attributePath = new AST\AttributePath($scheme, explode('.', $name));

        if ($stream->hasNext() === false) {
            return $attributePath;
        }

        // Value path
        return $this->parseValuePath($stream, $attributePath);
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

        try {
            return parent::parseInner($stream, $inValuePath);
        } catch (\Nette\Tokenizer\Exception $ex) {
            throw new InvalidValuePathException($stream);
        }
    }

    /**
     * @inheritDoc
     */
    protected function parseValuePath(Tokenizer\Stream $stream, AST\AttributePath $attributePath): AST\ValuePath
    {
        $valuePath = parent::parseValuePath($stream, $attributePath);

        // Sub attribute
        if ($stream->hasNext()) {
            $subAttr = $stream->matchNext(self::T_SUBATTR)->value;

            // Strip off the '.' at the start
            $subAttr = ltrim($subAttr, '.');

            // NOTE: T_SUBATTR matches end of string so no need to check if stream is at the end.
            return $valuePath->withSubAttribute($subAttr);
        }

        return $valuePath;
    }
}
