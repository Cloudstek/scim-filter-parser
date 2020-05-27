<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Exception;

use Cloudstek\SCIM\FilterParser\Tokenizer;
use Nette\Tokenizer\Token;

/**
 * Tokenizer exception.
 */
class TokenizerException extends \Nette\Tokenizer\Exception
{
    private ?Token $currentToken = null;

    /**
     * Invalid value path exception.
     *
     * @param string                $message  Exception message.
     * @param Tokenizer\Stream|null $stream   Tokenizer stream.
     * @param int                   $code     Exception code.
     * @param \Throwable|null       $previous Previous exception.
     */
    public function __construct(
        $message,
        ?Tokenizer\Stream $stream = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        if ($stream !== null) {
            $this->currentToken = $stream->currentToken();
        }
    }

    /**
     * Get token at which exception was thrown.
     *
     * @return Token|null
     */
    public function getCurrentToken(): ?Token
    {
        return $this->currentToken;
    }
}
