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
        // Pattern Modifiers (@see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php)
        // - u: Pattern and subject strings are treated as UTF-8.
        // - D: A dollar metacharacter in the pattern matches only at the end of the subject string.
        return preg_replace('/^[\s\0\x0B\x0C]+|[\s\0\x0B\x0C]+$/uD', '', $string);
    }
}
