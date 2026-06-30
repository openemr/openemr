<?php

/**
 * UDS age grouping used to split Table 4 insurance lines into the two reported
 * columns: 0–17 and 18 and over.
 *
 * Unit enum: runtime state derived from a patient's age at the reporting "as
 * of" date. Matched exhaustively so a new grouping forces decisions here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use DomainException;

enum UdsAgeGroup
{
    case Under18;
    case EighteenAndOver;

    /**
     * UDS splits Table 4 insurance at age 18: 0–17 vs 18 and over. A negative
     * age is rejected rather than guessed — the boundary must resolve a valid
     * age before classifying (parse, don't validate).
     */
    public static function fromAge(int $age): self
    {
        if ($age < 0) {
            throw new DomainException('Age cannot be negative');
        }

        return $age < 18 ? self::Under18 : self::EighteenAndOver;
    }

    public function label(): string
    {
        return match ($this) {
            self::Under18 => '0–17',
            self::EighteenAndOver => '18 and over',
        };
    }
}
