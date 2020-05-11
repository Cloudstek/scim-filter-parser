<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Exception;

/**
 * Invalid value path filter exception.
 */
class InvalidValuePathFilterException extends \Nette\Tokenizer\Exception
{
    /**
     * @inheritDoc
     */
    public function __construct($message = 'Invalid value path filter.', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
