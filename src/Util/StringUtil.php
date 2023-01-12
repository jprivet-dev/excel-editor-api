<?php

declare(strict_types=1);

namespace App\Util;

abstract class StringUtil
{
    /**
     * Remove all types of spaces.
     *
     * @param string $string
     * @return string
     *
     * @see https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/String/AbstractUnicodeString.php#L370
     */
    public static function trim(string $string): string
    {
        $chars = " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}";

        return preg_replace("{^[$chars]++|[$chars]++$}uD", '', $string);
    }
}
