<?php

namespace App\Tests\Util;

use App\Util\StringUtil;
use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
    /**
     * @dataProvider provideString
     */
    public function testTrim(string $expected, string $string): void
    {
        $this->assertSame($expected, StringUtil::trim($string));
    }

    public function provideString(): \Generator
    {
        yield ['hello', "hello"];

        yield ['hello', " hello"];
        yield ['hello', "hello "];
        yield ['hello', " hello "];
        yield ['hello', "   hello"];
        yield ['hello', "hello   "];
        yield ['hello', "   hello   "];

        yield ['hello', "\thello"];
        yield ['hello', "hello\t"];
        yield ['hello', "\thello\t"];
        yield ['hello', "\t\t\thello"];
        yield ['hello', "hello\t\t\t"];
        yield ['hello', "\t\t\thello\t\t\t"];

        yield ['hello', "\nhello"];
        yield ['hello', "hello\n"];
        yield ['hello', "\nhello\n"];
        yield ['hello', "\n\n\nhello"];
        yield ['hello', "hello\n\n\n"];
        yield ['hello', "\n\n\nhello\n\n\n"];

        yield ['hello', "\rhello"];
        yield ['hello', "hello\r"];
        yield ['hello', "\rhello\r"];
        yield ['hello', "\r\r\rhello"];
        yield ['hello', "hello\r\r\r"];
        yield ['hello', "\r\r\rhello\r\r\r"];

        yield ['hello', "\0hello"];
        yield ['hello', "hello\0"];
        yield ['hello', "\0hello\0"];
        yield ['hello', "\0\0\0hello"];
        yield ['hello', "hello\0\0\0"];
        yield ['hello', "\0\0\0hello\0\0\0"];

        yield ['hello', "\x0Bhello"];
        yield ['hello', "hello\x0B"];
        yield ['hello', "\x0Bhello\x0B"];
        yield ['hello', "\x0B\x0B\x0Bhello"];
        yield ['hello', "hello\x0B\x0B\x0B"];
        yield ['hello', "\x0B\x0B\x0Bhello\x0B\x0B\x0B"];

        yield ['hello', "\x0Chello"];
        yield ['hello', "hello\x0C"];
        yield ['hello', "\x0Chello\x0C"];
        yield ['hello', "\x0C\x0C\x0Chello"];
        yield ['hello', "hello\x0C\x0C\x0C"];
        yield ['hello', "\x0C\x0C\x0Chello\x0C\x0C\x0C"];
    }
}
