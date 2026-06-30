<?php

/**
 * Aggregates parsed per-patient records into a UDS Patients by ZIP Code Table.
 *
 * Pure and deterministic: one pass over the records, no database, clock, or
 * global state. Residence rows are created as ZIPs are first seen; an
 * unclassified payer is counted as Uninsured (the ZIP table has no "unknown
 * insurance" column), so the grand total always equals the patient count.
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

final class ZipCodeTableReportBuilder
{
    /**
     * @param iterable<ZipCodeTablePatientRecord> $records
     */
    public function build(iterable $records): ZipCodeTableReport
    {
        $residences = [];
        $counts = [];

        foreach ($records as $record) {
            $key = $record->residence->key();
            if (!isset($residences[$key])) {
                $residences[$key] = $record->residence;
                $counts[$key] = $this->zeroedColumns();
            }

            $category = $record->payerCategory ?? UdsPayerCategory::None;
            $column = UdsZipInsuranceColumn::fromPayerCategory($category);
            $counts[$key][$column->value]++;
        }

        return new ZipCodeTableReport($residences, $counts);
    }

    /**
     * @return array<string, int>
     */
    private function zeroedColumns(): array
    {
        $columns = [];
        foreach (UdsZipInsuranceColumn::cases() as $column) {
            $columns[$column->value] = 0;
        }

        return $columns;
    }
}
