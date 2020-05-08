<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tokenizer;

/**
 * Tokenizer class.
 */
class Tokenizer extends \Nette\Tokenizer\Tokenizer
{
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
