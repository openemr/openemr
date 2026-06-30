<?php

/**
 * A UDS Table 3B race category, at the manual's reported granularity.
 *
 * The detailed Asian (Lines 1a–1g) and Native Hawaiian/Other Pacific Islander
 * (Lines 2a–2d) sub-categories roll up into the Total Asian (Line 1) and Total
 * NHOPI (Line 2) lines; the remaining categories are themselves single lines
 * (Black 3, AI/AN 4, White 5, More than one race 6, Unreported 7). `rollupLine()`
 * gives the line a category contributes to, so the aggregator computes the
 * sub-totals without hard-coding membership.
 *
 * Backed because it is reported. Matched exhaustively.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

enum UdsRaceCategory: string
{
    case AsianIndian = 'asian_indian';
    case Chinese = 'chinese';
    case Filipino = 'filipino';
    case Japanese = 'japanese';
    case Korean = 'korean';
    case Vietnamese = 'vietnamese';
    case OtherAsian = 'other_asian';
    case NativeHawaiian = 'native_hawaiian';
    case OtherPacificIslander = 'other_pacific_islander';
    case GuamanianOrChamorro = 'guamanian_or_chamorro';
    case Samoan = 'samoan';
    case BlackOrAfricanAmerican = 'black_or_african_american';
    case AmericanIndianAlaskaNative = 'american_indian_alaska_native';
    case White = 'white';
    case MoreThanOneRace = 'more_than_one_race';
    case Unreported = 'unreported';

    /**
     * The Table 3B line this category is counted on. Asian sub-categories report
     * on Line 1 and NHOPI sub-categories on Line 2; the rest have their own line.
     */
    public function rollupLine(): int
    {
        return match ($this) {
            self::AsianIndian, self::Chinese, self::Filipino, self::Japanese,
            self::Korean, self::Vietnamese, self::OtherAsian => 1,
            self::NativeHawaiian, self::OtherPacificIslander,
            self::GuamanianOrChamorro, self::Samoan => 2,
            self::BlackOrAfricanAmerican => 3,
            self::AmericanIndianAlaskaNative => 4,
            self::White => 5,
            self::MoreThanOneRace => 6,
            self::Unreported => 7,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::AsianIndian => 'Asian Indian',
            self::Chinese => 'Chinese',
            self::Filipino => 'Filipino',
            self::Japanese => 'Japanese',
            self::Korean => 'Korean',
            self::Vietnamese => 'Vietnamese',
            self::OtherAsian => 'Other Asian',
            self::NativeHawaiian => 'Native Hawaiian',
            self::OtherPacificIslander => 'Other Pacific Islander',
            self::GuamanianOrChamorro => 'Guamanian or Chamorro',
            self::Samoan => 'Samoan',
            self::BlackOrAfricanAmerican => 'Black or African American',
            self::AmericanIndianAlaskaNative => 'American Indian/Alaska Native',
            self::White => 'White',
            self::MoreThanOneRace => 'More than one race',
            self::Unreported => 'Unreported / Chose not to disclose race',
        };
    }
}
