<?php

/**
 * A computed UDS Patients by ZIP Code Table.
 *
 * One row per residence (each ZIP seen, plus the Unknown Residence bucket) with
 * a count in each of the four insurance columns. Column totals feed the
 * cross-table reconciliation (ZIP Column B/C/D/E vs Table 4 insurance lines);
 * the grand total equals the unduplicated patient count. A pure value object.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class ZipCodeTableReport
{
    /**
     * @param array<string, ZipResidence> $residences residence key => residence
     * @param array<string, array<string, int>> $counts residence key =>
     *        (UdsZipInsuranceColumn value => count)
     */
    public function __construct(
        private array $residences,
        private array $counts,
    ) {
    }

    /**
     * The residence rows present in the report, in first-seen order.
     *
     * @return list<ZipResidence>
     */
    public function residences(): array
    {
        return array_values($this->residences);
    }

    public function count(ZipResidence $residence, UdsZipInsuranceColumn $column): int
    {
        return $this->counts[$residence->key()][$column->value] ?? 0;
    }

    public function residenceTotal(ZipResidence $residence): int
    {
        return array_sum($this->counts[$residence->key()] ?? []);
    }

    public function columnTotal(UdsZipInsuranceColumn $column): int
    {
        $total = 0;
        foreach ($this->counts as $byColumn) {
            $total += $byColumn[$column->value] ?? 0;
        }

        return $total;
    }

    public function total(): int
    {
        $total = 0;
        foreach ($this->counts as $byColumn) {
            $total += array_sum($byColumn);
        }

        return $total;
    }
}
