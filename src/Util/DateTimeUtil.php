<?php

declare(strict_types=1);

namespace App\Util;

abstract class DateTimeUtil
{
    public static function yearToDateTime(?string $year): ?\DateTimeImmutable
    {
        $year = trim($year);

        return $year ? \DateTimeImmutable::createFromFormat('Y', $year) : null;
    }
}
