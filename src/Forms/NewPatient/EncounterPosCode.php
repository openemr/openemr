<?php

/**
 * The Place of Service the encounter form preselects.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\NewPatient;

/**
 * The POS select is always in the encounter form (hidden unless "Set POS code
 * in encounter" is on), so whatever it preselects is what gets saved. With
 * nothing selected the browser posts the first option, 01 (Pharmacy), and
 * claims go out with that POS.
 */
final class EncounterPosCode
{
    /**
     * An existing encounter's saved code, else the facility's.
     *
     * @param mixed $savedPosCode    form_encounter.pos_code of the encounter being viewed
     * @param mixed $facilityPosCode pos_code of the encounter's facility
     */
    public static function toSelect(bool $existingEncounter, mixed $savedPosCode, mixed $facilityPosCode): ?string
    {
        $saved = self::code($savedPosCode);
        if ($existingEncounter && $saved !== null) {
            return $saved;
        }
        return self::code($facilityPosCode);
    }

    private static function code(mixed $value): ?string
    {
        if (is_int($value)) {
            $value = (string) $value;
        }
        if (!is_string($value) || $value === '' || $value === '0') {
            return null;
        }
        return $value;
    }
}
