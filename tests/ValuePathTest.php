<?php

declare(strict_types=1);

namespace Cloudstek\SCIM\FilterParser\Tests;

use Cloudstek\SCIM\FilterParser\Exception\InvalidValuePathFilterException;
use Cloudstek\SCIM\FilterParser\FilterParser;
use PHPUnit\Framework\TestCase;

class ValuePathTest extends TestCase
{
    private static FilterParser $parser;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$parser = new FilterParser();
    }

    public function testValuePathNestedThrowsException()
    {
        $this->expectException(InvalidValuePathFilterException::class);
        $this->expectExceptionMessage('Invalid value path filter.');

        self::$parser->parse('name[formatted[foo eq "bar"]]');
    }

    public function testValuePathComplexNestedThrowsException()
    {
        $this->expectException(InvalidValuePathFilterException::class);
        $this->expectExceptionMessage('Invalid value path filter.');

        self::$parser->parse('name[not (foo eq "bar" and formatted[foo eq "baz"])]');
    }
}
