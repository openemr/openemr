<?php

/**
 * UDS Table 4 special-medically-underserved-populations section (Lines 14–26).
 *
 * The migratory/seasonal breakout (Lines 14/15) and the homeless housing-type
 * breakout (Lines 17–22) are per-subtype counts; the corresponding totals
 * (Line 16 agricultural workers, Line 23 homeless) count distinct patients with
 * any status in that population, so a patient who is both migratory and seasonal
 * counts in each breakout line but only once in the total. Awardee-type gating
 * of which breakout lines a given center actually reports is a later
 * reporting-layer concern (UDS-DATA-MODEL-VALIDATION.md §3).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use OpenEMR\FQHC\SpecialPopulation\HomelessStatus;

final readonly class Table4SpecialPopulationsSection
{
    /**
     * @param array<string, int> $homelessByType count keyed by HomelessStatus value
     */
    public function __construct(
        public int $migratoryAgriculturalWorkers,
        public int $seasonalAgriculturalWorkers,
        public int $totalAgriculturalWorkers,
        private array $homelessByType,
        public int $totalHomeless,
        public int $schoolBased,
        public int $veterans,
        public int $publicHousing,
    ) {
    }

    public function homeless(HomelessStatus $status): int
    {
        return $this->homelessByType[$status->value] ?? 0;
    }
}
