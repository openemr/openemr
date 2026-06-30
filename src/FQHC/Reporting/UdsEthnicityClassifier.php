<?php

/**
 * Classifies an OpenEMR patient ethnicity value into a UDS Table 3B ethnicity
 * column.
 *
 * The input is `patient_data.ethnicity` — an option_id from the `ethnicity`
 * list. OpenEMR records ethnicity only at the binary level (Hispanic / Not
 * Hispanic), so a Hispanic patient maps to the "Hispanic, combined" column
 * (a5): the Mexican / Puerto Rican / Cuban detail UDS also offers is not stored
 * and would need a new capture field. A missing, declined, or unrecognised
 * value resolves to Unreported — ethnicity is never fabricated.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class UdsEthnicityClassifier
{
    public function classify(?string $ethnicityOptionId): UdsEthnicityCategory
    {
        return match ($ethnicityOptionId) {
            'hisp_or_latin' => UdsEthnicityCategory::Combined,
            'not_hisp_or_latin' => UdsEthnicityCategory::NotHispanic,
            default => UdsEthnicityCategory::Unreported,
        };
    }
}
