<?php

/**
 * A computed UDS Table 3A ("Patients by Age and by Sex").
 *
 * Holds the male/female patient count for each of the 38 age-band lines; the
 * total (Line 39) is the sum of Lines 1–38 across both columns. A pure value
 * object — the same records always yield the same numbers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class Table3aReport
{
    /**
     * @param array<int, array<string, int>> $counts patient count keyed by
     *        Table 3A line number (1–38), then by UdsSex case name
     */
    public function __construct(private array $counts)
    {
    }

    public function count(Table3aAgeBand $ageBand, UdsSex $sex): int
    {
        return $this->counts[$ageBand->line][$sex->name] ?? 0;
    }

    public function sexTotal(UdsSex $sex): int
    {
        $total = 0;
        foreach ($this->counts as $bySex) {
            $total += $bySex[$sex->name] ?? 0;
        }

        return $total;
    }

    /**
     * Total patients (Line 39).
     */
    public function total(): int
    {
        $total = 0;
        foreach ($this->counts as $bySex) {
            $total += array_sum($bySex);
        }

        return $total;
    }
}
