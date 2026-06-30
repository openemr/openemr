<?php

/**
 * One patient's resolved inputs for the UDS patient-characteristics tables.
 *
 * The data boundary (a ReportingPatientSource) reads OpenEMR and produces this
 * value object: the age is already computed and the FPL band already calculated,
 * but the demographic and insurance values are still the raw OpenEMR codes — the
 * pure record factory runs the classifiers over them. Age and the demographic
 * codes are nullable because intake data is often incomplete; the factory
 * decides what that means per table rather than this object guessing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulationStatus;

final readonly class ReportingPatient
{
    /**
     * @param list<SpecialPopulationStatus> $specialPopulations
     */
    public function __construct(
        public int $pid,
        public ?int $ageYears,
        public ?string $sexCode,
        public ?string $raceCode,
        public ?string $ethnicityCode,
        public ?string $languageCode,
        public ?string $interpreterNeeded,
        public ?string $zip,
        public FplBand $incomeBand,
        public ?int $insuranceTypeCode,
        public array $specialPopulations = [],
    ) {
    }
}
