<?php

declare(strict_types=1);

namespace OpenEMR\BC;

use DateTimeInterface;

/**
 * Do not create new code that references this class. It should only be used to
 * encapsulate and highlight antipatterns to ease migration towards best
 * practices.
 */
class Utilities
{
    public static function isDateEmpty(mixed $date): bool
    {
        if ($date instanceof DateTimeInterface) {
            return false;
        }
        return match ($date) {
            null => true,
            '' => true,
            '0000-00-00' => true,
            '0000-00-00 00:00:00' => true,
            '00/00/0000' => true,
            '00-00-0000' => true,
            default => false,
        };
    }
}
