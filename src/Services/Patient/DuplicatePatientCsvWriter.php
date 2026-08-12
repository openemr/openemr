<?php

/**
 * DuplicatePatientCsvWriter serialises the Duplicate Patient Management report as a spreadsheet.
 *
 * Every cell goes through csvEscape(), which strips the characters a spreadsheet would interpret as
 * a formula. The column order matches the HTML table so the two views stay recognisably the same
 * report.
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
     * @param list<DuplicatePatientGroup> $groups
     */
    public function write(array $groups): string
    {
        // Spelled out rather than looped so the translation extractor, which scans for literal
        // xl() arguments, actually finds these headings.
        $csv = $this->toLine([
            xl('Group'),
            xl('Score'),
            xl('PID'),
            xl('Public'),
            xl('Scope'),
            xl('Name'),
            xl('DOB'),
            xl('Gender'),
            xl('Email'),
            xl('Telephone'),
            xl('Registered'),
            xl('Address'),
        ]);

        foreach ($groups as $group) {
            foreach ($group->getRows() as $row) {
                $csv .= $this->toLine([
                    (string) $group->number,
                    (string) $row->score,
                    (string) $row->pid,
                    $row->publicId,
                    $this->translateScope($row->scopeLabel),
                    $row->name,
                    $row->dateOfBirth,
                    $row->sex,
                    $row->email,
                    $row->phones,
                    $row->registeredOn,
                    $row->street,
                ]);
            }
        }

        return $csv;
    }

    public function buildFilename(string $instanceName, string $timestamp): string
    {
        return "duplicate_patients_" . $instanceName . "_" . $timestamp . ".csv";
    }

    /**
     * The scope label is a closed set, so it is translated by match rather than by passing a
     * runtime string to xl() -- which would neither type-check nor be found by the extractor.
     */
    private function translateScope(string $scopeLabel): string
    {
        return match ($scopeLabel) {
            DuplicatePatientRow::SCOPE_MERGE_FROM => xl('Merge From'),
            DuplicatePatientRow::SCOPE_MERGE_TO => xl('Merge To'),
            default => '',
        };
    }

    /**
     * @param list<string> $cells
     */
    private function toLine(array $cells): string
    {
        return implode(',', array_map(csvEscape(...), $cells)) . "\n";
    }
}
