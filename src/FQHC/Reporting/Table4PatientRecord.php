<?php

/**
 * One patient's already-resolved characteristics for UDS Table 4.
 *
 * This is the parsed reporting input: the income band, principal-insurance
 * category, age group, and special-population statuses have all been determined
 * at the data boundary, so the aggregator works only with valid, typed values.
 * The payer category is nullable here because an unrecognised insurance type
 * cannot be classified for the Snapshot; the aggregator coerces that null to
 * None/Uninsured (UDS Table 4 has no "unknown insurance" line — see
 * UDS-DATA-MODEL-VALIDATION.md §4).
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
use OpenEMR\FQHC\Payer\UdsPayerCategory;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulationStatus;

final readonly class Table4PatientRecord
{
    /**
     * @param list<SpecialPopulationStatus> $specialPopulationStatuses statuses
     *        the patient held at any point in the reporting year
     */
    public function __construct(
        public FplBand $incomeBand,
        public ?UdsPayerCategory $payerCategory,
        public UdsAgeGroup $ageGroup,
        public array $specialPopulationStatuses = [],
    ) {
    }
}
