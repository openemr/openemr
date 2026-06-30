<?php

/**
 * Classifies an OpenEMR patient sex value into a UDS Table 3A sex column.
 *
 * The input is `patient_data.sex` — an option_id from the `sex` list (`Male`,
 * `Female`, `UNK`). Table 3A has only male and female columns, so an unknown or
 * unrecognised value returns null; the caller (the report assembly) decides how
 * to handle a patient who cannot be placed in either column, rather than this
 * classifier guessing one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class UdsSexClassifier
{
    public function classify(?string $sexOptionId): ?UdsSex
    {
        return match ($sexOptionId) {
            'Male' => UdsSex::Male,
            'Female' => UdsSex::Female,
            default => null,
        };
    }
}
