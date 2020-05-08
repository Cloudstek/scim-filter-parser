<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser;

use Nette\Tokenizer\Exception as TokenizerException;

/**
 * SCIM Filter Parser.
 */
class FilterParser implements FilterParserInterface
{
    private const T_NUMBER = 10;
    private const T_STRING = 11;
    private const T_BOOL = 12;
    private const T_NULL = 13;

    private const T_PAREN_OPEN = 21;
    private const T_PAREN_CLOSE = 22;
    private const T_BRACKET_OPEN = 23;
    private const T_BRACKET_CLOSE = 24;

    private const T_NAME = 30;

    private const T_NEGATION = 40;
    private const T_LOG_OP = 41;
    private const T_COMP_OP = 42;

    private Tokenizer\Tokenizer $tokenizer;

    /**
     * SCIM Filter Parser.
     */
    public function __construct()
    {
        $this->tokenizer = new Tokenizer\Tokenizer(
            [
                self::T_NUMBER => '\d+(?:\.\d+)?',
                self::T_STRING => '\"[^\"]*\"',
                self::T_BOOL => 'true|false',
                self::T_NULL => 'null',
                self::T_PAREN_OPEN => '\(',
                self::T_PAREN_CLOSE => '\)',
                self::T_BRACKET_OPEN => '\[',
                self::T_BRACKET_CLOSE => '\]',
                self::T_NEGATION => 'not\s+',
                self::T_LOG_OP => '\s+(?:and|or)\s+',
                self::T_COMP_OP => '\s(?:eq|ne|co|sw|ew|gt|lt|ge|le|pr)\s+',
                self::T_NAME => '(?:(?:[^\"]+\:)+)?[\-\_a-z0-9]+(?:\.[\-\_a-z0-9]+)?',
            ],
            'i'
        );
    }

    /**
     * Parse filter string.
     *
     * @param string $input SCIM filter.
     *
     * @throws TokenizerException
     *
     * @return AST\Node|null
     */
    public function parse(string $input): ?AST\Node
    {
        $stream = $this->tokenizer->tokenize($input);

        return $this->parseFilter($stream);
    }

    /**
     * Parse filter.
     *
     * @param Tokenizer\Stream $stream
     * @param bool             $inValuePath
     *
     * @throws TokenizerException
     *
     * @return AST\Node|null
     */
    private function parseFilter(Tokenizer\Stream $stream, bool $inValuePath = false): ?AST\Node
    {
        $node = null;

        // Parentheses
        if ($stream->isNext(self::T_PAREN_OPEN)) {
            $node = $this->parseParentheses($stream);

            if ($node === null) {
                return null;
            }
        }

        // Negation
        if ($stream->isNext(self::T_NEGATION)) {
            $node = $this->parseNegation($stream, $inValuePath);

            if ($node === null) {
                return null;
            }
        }

        // Comparison or value path
        if ($stream->isNext(self::T_NAME)) {
            $node = $this->parseAttributePath($stream, $inValuePath);
        }

        if ($node !== null) {
            // Logical connective
            if ($stream->isNext(self::T_LOG_OP)) {
                return $this->parseConnective($stream, $node, $inValuePath);
            }

            return $node;
        }

        throw new TokenizerException(
            sprintf(
                'Expected an attribute/value path, opening parenthesis or a negation, got "%s".',
                $stream->nextValue()
            )
        );
    }

    /**
     * Parse filter in parentheses.
     *
     * @param Tokenizer\Stream $stream
     *
     * @throws TokenizerException
     *
     * @return AST\Node|null
     */
    private function parseParentheses(Tokenizer\Stream $stream): ?AST\Node
    {
        $stream->matchNext(self::T_PAREN_OPEN);
        $filter = $stream->joinUntil(self::T_PAREN_CLOSE);
        $stream->matchNext(self::T_PAREN_CLOSE);

        if (empty($filter)) {
            return null;
        }

        return $this->parse($filter);
    }

    /**
     * Parse negation.
     *
     * @param Tokenizer\Stream $stream
     * @param bool             $inValuePath
     *
     * @throws TokenizerException
     *
     * @return AST\Node|null
     */
    private function parseNegation(Tokenizer\Stream $stream, bool $inValuePath = false): ?AST\Node
    {
        $stream->matchNext(self::T_NEGATION);
        $stream->matchNext(self::T_PAREN_OPEN);
        $filter = $stream->joinUntil(self::T_PAREN_CLOSE);
        $stream->matchNext(self::T_PAREN_CLOSE);

        if (empty($filter)) {
            return null;
        }

        $node = $this->parseFilter($this->tokenizer->tokenize($filter), $inValuePath);

        return new AST\Negation($node);
    }

    /**
     * Parse attribute path.
     *
     * @param Tokenizer\Stream $stream
     * @param bool             $inValuePath
     *
     * @throws TokenizerException
     *
     * @return AST\Node
     */
    private function parseAttributePath(Tokenizer\Stream $stream, bool $inValuePath = false): AST\Node
    {
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

        // Value path (only if not in value path already
        if ($stream->isNext(self::T_BRACKET_OPEN) && $inValuePath === false) {
            return $this->parseValuePath($stream, $attributePath);
        }

        // Comparison
        return $this->parseComparison($stream, $attributePath);
    }

    /**
     * Parse value path.
     *
     * @param Tokenizer\Stream  $stream
     * @param AST\AttributePath $attributePath
     *
     * @throws TokenizerException
     *
     * @return AST\Node
     */
    private function parseValuePath(Tokenizer\Stream $stream, AST\AttributePath $attributePath): AST\Node
    {
        $stream->matchNext(self::T_BRACKET_OPEN);

        $subNode = $this->parseFilter($stream, true);

        $stream->matchNext(self::T_BRACKET_CLOSE);

        return $this->prependNodeAttributePath($attributePath, $subNode);
    }

    /**
     * Parse comparison.
     *
     * @param Tokenizer\Stream  $stream
     * @param AST\AttributePath $attributePath
     *
     * @throws TokenizerException
     *
     * @return AST\Connective|AST\Comparison
     */
    private function parseComparison(
        Tokenizer\Stream $stream,
        AST\AttributePath $attributePath
    ) {
        $operator = trim($stream->matchNext(self::T_COMP_OP)->value);
        $value = null;

        if (strcasecmp($operator, 'pr') <> 0) {
            $value = $stream->matchNext(self::T_STRING, self::T_NUMBER, self::T_BOOL, self::T_NULL);

            switch ($value->type) {
                case self::T_STRING:
                    $value = trim($value->value, '"');
                    break;
                case self::T_NUMBER:
                    if (strpos($value->value, '.') !== false) {
                        $value = floatval($value->value);
                        break;
                    }

                    $value = intval($value->value);
                    break;
                case self::T_BOOL:
                    $value = strcasecmp($value->value, 'true') === 0;
                    break;
                default:
                    $value = null;
                    break;
            }
        }

        return new AST\Comparison($attributePath, $operator, $value);
    }

    /**
     * Parse logical connective (and/or).
     *
     * @param Tokenizer\Stream $stream
     * @param AST\Node         $leftNode
     * @param bool             $inValuePath
     *
     * @throws TokenizerException
     *
     * @return AST\Connective
     */
    private function parseConnective(
        Tokenizer\Stream $stream,
        AST\Node $leftNode,
        bool $inValuePath = false
    ): AST\Connective {
        $logOp = trim($stream->matchNext(self::T_LOG_OP)->value);

        $isConjunction = strcasecmp($logOp, 'and') === 0;

        // Parse right hand node
        $rightNode = $this->parseFilter($stream, $inValuePath);

        // Connective nodes
        $nodes = [$leftNode, $rightNode];

        // Merge consecutive connectives of the same type into one connective.
        if (($rightNode instanceof AST\Conjunction && $isConjunction === true)
            || ($rightNode instanceof AST\Disjunction && $isConjunction === false)) {
            $rightNodes = $rightNode->getNodes();

            $nodes = array_merge([$leftNode], $rightNodes);
        }

        return $isConjunction === true
            ? new AST\Conjunction($nodes)
            : new AST\Disjunction($nodes);
    }

    /**
     * Prepend attribute path to node.
     *
     * @param AST\AttributePath $attributePath
     * @param AST\Node          $node
     *
     * @return AST\Node
     */
    private function prependNodeAttributePath(AST\AttributePath $attributePath, AST\Node $node): AST\Node
    {
        if ($node instanceof AST\Comparison) {
            $names = array_merge($attributePath->getNames(), $node->getAttributePath()->getNames());

            $node->setAttributePath(new AST\AttributePath($attributePath->getSchema(), $names));

            return $node;
        }

        if ($node instanceof AST\Negation) {
            $node->setNode($this->prependNodeAttributePath($attributePath, $node->getNode()));

            return $node;
        }

        if ($node instanceof AST\Connective) {
            foreach ($node->getNodes() as $subNode) {
                $this->prependNodeAttributePath($attributePath, $subNode);
            }

            return $node;
        }

        return $node;
    }
}
