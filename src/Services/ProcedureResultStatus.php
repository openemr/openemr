<?php

/**
 * The statuses a laboratory result is stored with, and the FHIR Observation.status each is
 * served as.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

/**
 * Backed by the option ids of the seeded proc_res_status list, which the results screen stores
 * in procedure_result.result_status. Error is read as entered-in-error, as the maintainers read
 * it (#14960); the HL7 import also stores a result that could not be obtained (OBX-11 X) as error.
 */
enum ProcedureResultStatus: string
{
    case Final = 'final';
    case Preliminary = 'prelim';
    case Corrected = 'correct';
    case Cancelled = 'cancel';
    case Error = 'error';
    case Incomplete = 'incomplete';

    /**
     * The stored status a FHIR Observation.status is written as, or null for one that isn't
     * written: amended and unknown have no equivalent here, and registered means no result yet,
     * while a result is written with its value.
     */
    public static function fromFhir(string $status): ?self
    {
        return match ($status) {
            'final' => self::Final,
            'preliminary' => self::Preliminary,
            'corrected' => self::Corrected,
            'cancelled' => self::Cancelled,
            'entered-in-error' => self::Error,
            default => null,
        };
    }

    /**
     * The FHIR Observation.status this stored status is served as.
     */
    public function fhirStatus(): string
    {
        return match ($this) {
            self::Final => 'final',
            self::Preliminary => 'preliminary',
            self::Corrected => 'corrected',
            self::Cancelled => 'cancelled',
            self::Error => 'entered-in-error',
            // HL7 v2 result status I, "specimen in lab; results pending".
            self::Incomplete => 'registered',
        };
    }
}
