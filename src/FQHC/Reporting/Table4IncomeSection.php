<?php

/**
 * UDS Table 4 income-by-poverty-guideline section (Lines 1–6).
 *
 * Holds the patient count for each FPL band; the total (Line 6) is the sum of
 * Lines 1–5 including Unknown. Unknown patients are never redistributed into the
 * other bands (UDS-DATA-MODEL-VALIDATION.md §1).
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

final readonly class Table4IncomeSection
{
    /**
     * @param array<string, int> $counts patient count keyed by FplBand case name
     */
    public function __construct(private array $counts)
    {
    }

    public function count(FplBand $band): int
    {
        return $this->counts[$band->name] ?? 0;
    }

    public function total(): int
    {
        return array_sum($this->counts);
    }
}
