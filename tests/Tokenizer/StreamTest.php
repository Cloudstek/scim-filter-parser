<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests\Tokenizer;

use Cloudstek\SCIM\FilterParser\Tokenizer;
use Nette\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

/**
 * Tokenizer stream tests.
 *
 * @covers \Cloudstek\SCIM\FilterParser\Tokenizer\Stream
 */
class StreamTest extends TestCase
{
    public function testStreamMatchNextUnexpectedEndOfString()
    {
        $this->expectException(\Nette\Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected end of string.');

        $tokens = [
            new Token('userName', 1, 0),
            new Token(' eq ', 2, 8),
        ];

        $stream = new Tokenizer\Stream($tokens);

        $stream->matchNext(1);
        $stream->matchNext(2);
        $stream->matchNext(3);
    }

    public function testStreamMatchNextMismatch()
    {
        $this->expectException(\Nette\Tokenizer\Exception::class);
        $this->expectExceptionMessage('Unexpected "eq" on line 1, column 9.');

        $tokens = [
            new Token('userName', 1, 0),
            new Token(' eq ', 2, 8),
        ];

        $stream = new Tokenizer\Stream($tokens);

        $stream->matchNext(1);
        $stream->matchNext(3);
    }
}
