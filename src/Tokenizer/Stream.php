<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tokenizer;

use Nette\Tokenizer\Exception as TokenizerException;
use Nette\Tokenizer\Token;

/**
 * Stream class.
 */
class Stream extends \Nette\Tokenizer\Stream
{
    /**
     * Match next token.
     *
     * Make sure the next token matches a certain type or value and return it.
     *
     * @param int|string ...$args
     *
     * @throws TokenizerException When next token does not match the given type(s) or value(s).
     *
     * @return Token
     */
    public function matchNext(...$args): Token
    {
        $token = $this->nextToken();

        if ($token === null) {
            throw new TokenizerException('Unexpected end of string.');
        } elseif ($this->isCurrent(...$args) === false) {
            [$line, $col] = Tokenizer::getCoordinates($token->value, $token->offset);

            throw new TokenizerException(
                sprintf(
                    'Unexpected "%s" on line %d, column %d.',
                    trim($token->value),
                    $line,
                    $col
                )
            );
        }

        return $token;
    }
}
