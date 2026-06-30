<?php

/**
 * UDS income band as a percentage of the Federal Poverty Level.
 *
 * These are the Table 4 income lines: 100% and below, 101–150%, 151–200%,
 * over 200%, and Unknown. Unknown is a first-class case — when income or
 * household size is missing it must NOT default to "≤100%".
 *
 * Unit enum: runtime state derived from the income determination. Matching is
 * exhaustive (no default branch) so a new band cannot be silently dropped.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Fpl;

enum FplBand
{
    case AtOrBelow100;
    case From101To150;
    case From151To200;
    case Above200;
    case Unknown;

    public function label(): string
    {
        return match ($this) {
            self::AtOrBelow100 => '100% and below',
            self::From101To150 => '101–150%',
            self::From151To200 => '151–200%',
            self::Above200 => 'Over 200%',
            self::Unknown => 'Unknown',
        };
    }
}
