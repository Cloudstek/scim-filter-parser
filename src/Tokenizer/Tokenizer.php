<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tokenizer;

/**
 * Tokenizer.
 */
class Tokenizer extends \Nette\Tokenizer\Tokenizer
{
    /**
     * Tokenizer.
     *
     * @param array  $patterns
     * @param string $flags
     */
    public function __construct(array $patterns, string $flags = '')
    {
        ksort($patterns);

        parent::__construct($patterns, $flags);
    }

    /**
     * Tokenizes string.
     *
     * @param string $input
     *
     * @throws \Nette\Tokenizer\Exception
     *
     * @return Stream
     */
    public function tokenize(string $input): Stream
    {
        $stream = parent::tokenize($input);

        return new Stream($stream->tokens);
    }
}
