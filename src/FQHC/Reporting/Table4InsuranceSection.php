<?php

/**
 * UDS Table 4 principal-insurance section (Lines 7–12), split by age column.
 *
 * Each UDS payer category carries a count for the 0–17 and 18-and-over columns.
 * Every patient is classified into exactly one category — there is no "unknown
 * insurance" line, so the builder coerces an unresolved payer to None/Uninsured
 * before counting (UDS-DATA-MODEL-VALIDATION.md §4). The total (Line 12) equals
 * the patient total.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use OpenEMR\FQHC\Payer\UdsPayerCategory;

final readonly class Table4InsuranceSection
{
    /**
     * @param array<string, array<string, int>> $counts keyed by
     *        UdsPayerCategory value, then by UdsAgeGroup case name
     */
    public function __construct(private array $counts)
    {
    }

    public function count(UdsPayerCategory $category, UdsAgeGroup $ageGroup): int
    {
        return $this->counts[$category->value][$ageGroup->name] ?? 0;
    }

    public function categoryTotal(UdsPayerCategory $category): int
    {
        return array_sum($this->counts[$category->value] ?? []);
    }

    public function total(): int
    {
        $total = 0;
        foreach ($this->counts as $byAgeGroup) {
            $total += array_sum($byAgeGroup);
        }

        return $total;
    }
}
