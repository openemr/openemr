<?php

/**
 * Loads versioned Federal Poverty Level guidelines from `fqhc_fpl_guideline`.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Fpl;

use OpenEMR\Common\Database\QueryUtils;

final class FplGuidelineRepository
{
    /**
     * The most recent guideline year on file for the region, or null when the
     * reference table has not been seeded for it.
     */
    public function findLatestForRegion(FplRegion $region): ?FederalPovertyGuideline
    {
        $row = QueryUtils::querySingleRow(
            'SELECT guideline_year, base_annual, per_person_annual '
            . 'FROM fqhc_fpl_guideline WHERE region = ? ORDER BY guideline_year DESC LIMIT 1',
            [$region->value],
        );

        if (!is_array($row)) {
            return null;
        }

        $year = $row['guideline_year'] ?? null;
        $base = $row['base_annual'] ?? null;
        $perPerson = $row['per_person_annual'] ?? null;

        if (!is_numeric($year) || !is_numeric($base) || !is_numeric($perPerson)) {
            return null;
        }

        return new FederalPovertyGuideline((int) $year, $region, (float) $base, (float) $perPerson);
    }
}
