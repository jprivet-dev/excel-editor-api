<?php

declare(strict_types=1);

namespace App\Util;

abstract class StringUtil
{
    /**
     * Replace the accents in a string.
     *
     * @param string $string
     * @return string
     */
    public static function noAccent(string $string): string
    {
        $search = explode(
            ",",
            "Ç,Æ,Œ,Á,É,Í,Ó,Ú,À,È,Ì,Ò,Ù,Ä,Ë,Ï,Ö,Ü,Ÿ,Â,Ê,Î,Ô,Û,Å,Ø,U,ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,u"
        );

        $replace = explode(
            ",",
            "C,AE,OE,A,E,I,O,U,A,E,I,O,U,A,E,I,O,U,Y,A,E,I,O,U,A,O,U,c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,u"
        );

        return str_replace($search, $replace, $string);
    }

    /**
     * Convert a string into camelCase format.
     *
     * @param string $string
     * @return string
     *
     * @see https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/DependencyInjection/Compiler/RegisterServiceSubscribersPass.php#L117
     */
    public static function camelCase(string $string): string
    {
        $string = self::noAccent(strtolower($string));

        return lcfirst(str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9\x7f-\xff]++/', ' ', $string))));
    }

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
