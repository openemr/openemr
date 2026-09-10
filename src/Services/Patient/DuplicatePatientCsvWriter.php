<?php

/**
 * DuplicatePatientCsvWriter serialises the Duplicate Patient Management report as a spreadsheet.
 *
 * It walks the same {@see DuplicatePatientColumn} list the HTML table does, so the two views always
 * carry the same columns in the same order. Only the leading Group column is added here: the table
 * conveys grouping with a separator row, which a spreadsheet cannot.
 *
 * Every cell goes through csvEscape(), which strips the characters a spreadsheet would interpret as
 * the start of a formula.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2017-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

class DuplicatePatientCsvWriter
{
    /**
     * @param list<DuplicatePatientGroup>  $groups
     * @param list<DuplicatePatientColumn> $columns
     */
    public function write(array $groups, array $columns): string
    {
        $headings = [xl('Group')];
        foreach ($columns as $column) {
            $headings[] = $column->label;
        }
        $csv = $this->toLine($headings);

        foreach ($groups as $group) {
            foreach ($group->getRows() as $row) {
                $cells = [(string) $group->number];
                $values = $row->getValues();
                foreach ($columns as $column) {
                    $cells[] = $values[$column->key] ?? '';
                }
                $csv .= $this->toLine($cells);
            }
        }

        return $csv;
    }

    public function buildFilename(string $instanceName, string $timestamp): string
    {
        return "duplicate_patients_" . $instanceName . "_" . $timestamp . ".csv";
    }

    /**
     * @param list<string> $cells
     */
    private function toLine(array $cells): string
    {
        return implode(',', array_map(csvEscape(...), $cells)) . "\n";
    }
}
