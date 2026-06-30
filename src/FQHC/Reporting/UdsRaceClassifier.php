<?php

/**
 * Classifies an OpenEMR patient race value into a UDS Table 3B race category.
 *
 * The input is `patient_data.race` — an option_id from the `race` list. OpenEMR
 * ships both the five top-level OMB categories and the detailed Asian / Pacific
 * Islander subtypes UDS breaks out; this maps each to the corresponding
 * `UdsRaceCategory`. A generic "Asian" or "Native Hawaiian / Other Pacific
 * Islander" with no subtype lands in the Other-Asian / Other-Pacific-Islander
 * roll-up. A missing, declined, or unrecognised value resolves to Unreported
 * (Line 7) — race is never fabricated.
 *
 * OpenEMR stores a single race option per patient, so More-than-one-race cannot
 * be derived here; the detailed tribal/CDC race codes beyond the UDS breakout
 * fall through to Unreported and are a later CDC-code-range enhancement.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class UdsRaceClassifier
{
    public function classify(?string $raceOptionId): UdsRaceCategory
    {
        return match ($raceOptionId) {
            'asian_indian' => UdsRaceCategory::AsianIndian,
            'chinese' => UdsRaceCategory::Chinese,
            'filipino' => UdsRaceCategory::Filipino,
            'japanese' => UdsRaceCategory::Japanese,
            'korean' => UdsRaceCategory::Korean,
            'vietnamese' => UdsRaceCategory::Vietnamese,
            'Asian' => UdsRaceCategory::OtherAsian,
            'native_hawaiian' => UdsRaceCategory::NativeHawaiian,
            'guamanian_or_chamorro' => UdsRaceCategory::GuamanianOrChamorro,
            'samoan' => UdsRaceCategory::Samoan,
            'native_hawai_or_pac_island' => UdsRaceCategory::OtherPacificIslander,
            'black_or_afri_amer' => UdsRaceCategory::BlackOrAfricanAmerican,
            'amer_ind_or_alaska_native' => UdsRaceCategory::AmericanIndianAlaskaNative,
            'white' => UdsRaceCategory::White,
            default => UdsRaceCategory::Unreported,
        };
    }
}
