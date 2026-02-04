<?php

/**
 * PhoneType enum for phone number types
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

/**
 * Phone number types stored in the `phone_numbers.type` column.
 */
enum PhoneType: int
{
    case HOME = 1;
    case WORK = 2;
    case CELL = 3;
    case EMERGENCY = 4;
    case FAX = 5;

    /**
     * Get the translatable display label for this phone type.
     */
    public function label(): string
    {
        return match ($this) {
            self::HOME => xl('Home'),
            self::WORK => xl('Work'),
            self::CELL => xl('Cell'),
            self::EMERGENCY => xl('Emergency'),
            self::FAX => xl('Fax'),
        };
    }

    /**
     * Get all phone types as an array for select dropdowns.
     *
     * @return array<int, string> Array mapping type values to translated labels
     */
    public static function options(): array
    {
        return [
            self::HOME->value => self::HOME->label(),
            self::WORK->value => self::WORK->label(),
            self::CELL->value => self::CELL->label(),
            self::EMERGENCY->value => self::EMERGENCY->label(),
            self::FAX->value => self::FAX->label(),
        ];
    }

    /**
     * Check if a value matches this phone type.
     *
     * Accepts int or numeric string (as returned by database queries).
     *
     * @param int|string $value The value to check (typically from database)
     */
    public function matches(int|string $value): bool
    {
        return self::tryFrom((int) $value) === $this;
    }
}
