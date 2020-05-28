<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Exception;

use Nette\Tokenizer;

/**
 * Unexpected value exception.
 */
class UnexpectedValueException extends Tokenizer\Exception
{
    /**
     * Unexpected value exception.
     *
     * @param Tokenizer\Stream $stream
     * @param int              $code
     * @param \Throwable|null  $previous
     */
    public function __construct(Tokenizer\Stream $stream, $code = 0, \Throwable $previous = null)
    {
        $message = 'Unexpected value at line 1, column 1.';

        if ($stream->position < 0) {
            $stream->nextToken();
        }

        $token = $stream->currentToken();

        if ($token !== null) {
            // Get values from all tokens up to here.
            $values = array_map(
                fn($t) => $t->value,
                array_slice($stream->tokens, 0, $stream->position)
            );

            // Get coordinates.
            [$line, $col] = Tokenizer\Tokenizer::getCoordinates(
                implode('', $values),
                $token->offset
            );

            $message = sprintf(
                'Unexpected %s on line %d, column %d.',
                $token->value,
                (int)$line,
                (int)$col
            );
        }

        parent::__construct($message, $code, $previous);
    }
}
