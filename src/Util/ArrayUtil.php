<?php

declare(strict_types=1);

namespace App\Util;

abstract class ArrayUtil
{
    /**
     * Force on null all empty strings in an array.
     *
     * @param array $array
     * @return array
     */
    public static function emptyStringsAsNull(array $array): array
    {
        return array_map(fn($value) => empty($value) ? null : $value, $array);
    }

    /**
     * Trim all string values in an array.
     *
     * @param array $array
     * @return array
     */
    public static function trim(array $array): array
    {
        return array_map(
            fn($value) => is_string($value) ? StringUtil::trim($value) : $value,
            $array
        );
    }
}
