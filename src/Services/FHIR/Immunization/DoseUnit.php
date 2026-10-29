<?php

/**
 * An immunization dose's unit, between the Drug Units list (drug_units) that OpenEMR stores
 * by option id and the UCUM code FHIR's doseQuantity carries.
 *
 * The list's titles are not all UCUM: "mcg" is ug and "grams" is g in UCUM, and "mg/1cc" is
 * no unit at all. A title is sent as UCUM only when the UCUM engine accepts it or its alias.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\Immunization;

use OpenEMR\Cqm\Cql\Ucum\Ucum;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;

final class DoseUnit
{
    /**
     * Drug Units titles whose UCUM code is spelled differently.
     */
    private const UCUM_ALIASES = [
        'mcg' => 'ug',
        'grams' => 'g',
    ];

    /**
     * The UCUM code for a Drug Units title, or null when the title isn't a UCUM unit.
     */
    public static function ucumFor(string $title): ?string
    {
        $title = trim($title);
        // UCUM accepts a bare number as a unit of one; as a dose's unit it's the option id #14699 saw sent.
        if ($title === '' || is_numeric($title)) {
            return null;
        }
        $ucum = Ucum::instance();
        foreach ([$title, self::UCUM_ALIASES[strtolower($title)] ?? null] as $candidate) {
            if ($candidate !== null && $ucum->isValid($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * The Drug Units option id a doseQuantity names. With a code, the option whose title, or its
     * UCUM code, is that code; a code means what its system says, so only a UCUM code, or one with
     * no system, can name an option, and a code that names none is refused rather than read from
     * the unit text, which is only its display. With no code, the option whose title, in any case,
     * or UCUM code is the unit text. Null when no option fits.
     *
     * @param array<array-key, string> $options option id => title
     */
    public static function optionFor(?string $system, ?string $code, ?string $unit, array $options): ?string
    {
        $system = trim((string)$system);
        $code = trim((string)$code);
        $unit = trim((string)$unit);
        if ($code !== '') {
            if ($system !== '' && $system !== FhirCodeSystemConstants::UNITS_OF_MEASURE) {
                return null;
            }
            foreach ($options as $id => $title) {
                if ($title === $code || self::ucumFor($title) === $code) {
                    return (string)$id;
                }
            }
            return null;
        }
        if ($unit !== '') {
            foreach ($options as $id => $title) {
                if (strcasecmp(trim($title), $unit) === 0 || self::ucumFor($title) === $unit) {
                    return (string)$id;
                }
            }
        }
        return null;
    }
}
