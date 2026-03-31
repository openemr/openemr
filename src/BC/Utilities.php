<?php

declare(strict_types=1);

namespace OpenEMR\BC;

use DateTimeInterface;

/**
 * Do not create new code that references this class. It should only be used to
 * encapsulate and highlight antipatterns to ease migration towards best
 * practices.
 *
 * @deprecated
 */
class Utilities
{
    public static function isDateEmpty(mixed $date): bool
    {
        if ($date === null || $date === '') {
            return true;
        }
        if ($date instanceof DateTimeInterface) {
            return false;
        }
        if ($date === '0000-00-00') {
            return true;
        }
        if ($date === '0000-00-00 00:00:00') {
            return true;
        }
        if ($date === '00/00/0000') {
            return true;
        }
        if ($date === '00-00-0000') {
            return true;
        }

        return false;
    }
}
