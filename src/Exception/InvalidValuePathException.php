<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Exception;

use Cloudstek\SCIM\FilterParser\Tokenizer;

/**
 * Invalid value path exception.
 */
class InvalidValuePathException extends TokenizerException
{
    /**
     * Invalid value path exception.
     *
     * @param Tokenizer\Stream|null $stream   Tokenizer stream.
     * @param string                $message  Exception message.
     * @param int                   $code     Exception code.
     * @param \Throwable|null       $previous Previous exception.
     */
    public function __construct(
        ?Tokenizer\Stream $stream = null,
        $message = 'Invalid value path.',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $stream, $code, $previous);
    }
}
