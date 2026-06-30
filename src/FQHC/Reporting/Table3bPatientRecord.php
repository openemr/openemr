<?php

/**
 * One patient's already-resolved race, ethnicity, and language need for UDS
 * Table 3B.
 *
 * Parsed reporting input: the race category, Hispanic/Latino ethnicity column,
 * and whether the patient is best served in a language other than English have
 * all been determined at the data boundary, so the aggregator only sees valid,
 * typed values. (Mapping OpenEMR's CDC-coded race/ethnicity to these UDS
 * categories is a boundary concern handled in a later slice.)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class Table3bPatientRecord
{
    public function __construct(
        public UdsRaceCategory $race,
        public UdsEthnicityCategory $ethnicity,
        public bool $bestServedInNonEnglishLanguage,
    ) {
    }
}
