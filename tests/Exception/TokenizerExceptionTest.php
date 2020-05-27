<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests;

use Cloudstek\SCIM\FilterParser\Exception\TokenizerException;
use Cloudstek\SCIM\FilterParser\PathParser;
use Nette\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

/**
 * Tokenizer exception tests.
 *
 * @covers \Cloudstek\SCIM\FilterParser\Exception\TokenizerException
 * @covers \Cloudstek\SCIM\FilterParser\AbstractParser
 * @covers \Cloudstek\SCIM\FilterParser\PathParser
 * @covers \Cloudstek\SCIM\FilterParser\Tokenizer\Tokenizer
 */
class TokenizerExceptionTest extends TestCase
{
    public function testNegationAtStartThrowsException()
    {
        $parser = new PathParser();

        try {
            $parser->parse('not ()');
        } catch (\Exception $ex) {
            $this->assertInstanceOf(TokenizerException::class, $ex);

            /** @var Token $token */
            $token = $ex->getCurrentToken();

            $this->assertInstanceOf(Token::class, $token);
            $this->assertSame('not ', $token->value);
            $this->assertSame(30, $token->type);
            $this->assertSame(0, $token->offset);
        }
    }
}
