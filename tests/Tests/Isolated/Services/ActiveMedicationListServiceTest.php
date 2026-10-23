<?php

/**
 * Merge and de-dupe for the active medication list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

if (!defined('OPENEMR_STATIC_ANALYSIS')) {
    define('OPENEMR_STATIC_ANALYSIS', true);
}

use OpenEMR\Services\ActiveMedicationListService;
use PHPUnit\Framework\TestCase;

final class ActiveMedicationListServiceTest extends TestCase
{
    /**
     * Issues come first; a prescription with the same drug name is skipped.
     */
    public function testMergeDropsPrescriptionAlreadyOnIssueList(): void
    {
        $rows = ActiveMedicationListService::merge(
            [
                [
                    'title' => 'Lisinopril',
                    'drug_dosage_instructions' => '10 mg daily',
                    'begdate' => '2024-01-15',
                    'comments' => 'BP',
                ],
            ],
            [
                [
                    'drug' => 'lisinopril',
                    'dosage' => '10mg',
                    'drug_dosage_instructions' => '',
                    'start_date' => '2024-01-01',
                ],
                [
                    'drug' => 'Metformin',
                    'dosage' => '500 mg',
                    'drug_dosage_instructions' => 'BID',
                    'start_date' => '2025-06-01',
                ],
            ]
        );
        $this->assertCount(2, $rows);
        $this->assertSame('issue', $rows[0]['source']);
        $this->assertSame('Lisinopril', $rows[0]['title']);
        $this->assertSame('10 mg daily', $rows[0]['dose']);
        $this->assertSame('2024-01-15', $rows[0]['start']);
        $this->assertNull($rows[0]['end']);
        $this->assertSame('BP', $rows[0]['comments']);
        $this->assertSame('prescription', $rows[1]['source']);
        $this->assertSame('Metformin', $rows[1]['title']);
        $this->assertSame('500 mg BID', $rows[1]['dose']);
        $this->assertNull($rows[1]['end']);
    }

    /**
     * Empty titles and 0000-00-00 dates are not printable rows.
     */
    public function testMergeSkipsEmptyTitlesAndZeroDates(): void
    {
        $rows = ActiveMedicationListService::merge(
            [
                ['title' => '  ', 'begdate' => '2024-01-01'],
                ['title' => 'Aspirin', 'begdate' => '0000-00-00', 'comments' => ''],
            ],
            [
                ['drug' => '', 'start_date' => '2024-01-01'],
            ]
        );
        $this->assertCount(1, $rows);
        $this->assertSame('Aspirin', $rows[0]['title']);
        $this->assertNull($rows[0]['start']);
        $this->assertNull($rows[0]['end']);
    }

    /**
     * Prescription end dates copy onto the merged issue row.
     */
    public function testMergeCopiesEndDates(): void
    {
        $rows = ActiveMedicationListService::merge(
            [
                [
                    'title' => 'Atenolol',
                    'begdate' => '2023-03-01',
                    'enddate' => '2025-12-01',
                    'comments' => 'stopped',
                ],
            ],
            [
                [
                    'drug' => 'Old statin',
                    'dosage' => '20 mg',
                    'start_date' => '2020-01-01',
                    'end_date' => '2021-01-01',
                ],
            ]
        );
        $this->assertCount(2, $rows);
        $this->assertSame('2025-12-01', $rows[0]['end']);
        $this->assertSame('2021-01-01', $rows[1]['end']);
    }

    /**
     * Name de-dupe is case-insensitive.
     */
    public function testExcludeListedNamesIsCaseInsensitive(): void
    {
        $inactive = ActiveMedicationListService::merge(
            [],
            [
                [
                    'drug' => 'lisinopril',
                    'start_date' => '2020-01-01',
                    'end_date' => '2021-01-01',
                ],
                [
                    'drug' => 'Old statin',
                    'start_date' => '2020-01-01',
                    'end_date' => '2021-01-01',
                ],
            ]
        );
        $active = [
            ['title' => 'Lisinopril'],
        ];
        $rows = ActiveMedicationListService::excludeListedNames($inactive, $active);
        $this->assertCount(1, $rows);
        $this->assertSame('Old statin', $rows[0]['title']);
    }

    /**
     * Impossible calendar dates become null instead of a fake start or end.
     */
    public function testMergeDropsImpossibleCalendarDates(): void
    {
        $rows = ActiveMedicationListService::merge(
            [
                [
                    'title' => 'Aspirin',
                    'begdate' => '2026-02-31',
                    'enddate' => '2026-13-01',
                ],
            ],
            []
        );
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['start']);
        $this->assertNull($rows[0]['end']);
    }

    /**
     * A valid date prefix with trailing junk is not a date.
     */
    public function testMergeRejectsTrailingJunkOnDates(): void
    {
        $rows = ActiveMedicationListService::merge(
            [
                [
                    'title' => 'Metformin',
                    'begdate' => '2026-02-28-invalid',
                    'enddate' => '2026-02-28 08:15:00',
                ],
            ],
            []
        );
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['start']);
        $this->assertSame('2026-02-28 08:15:00', $rows[0]['end']);
    }

    /**
     * The eRx SQL fragment applies to both lists and prescriptions columns.
     */
    public function testErxExcludeSqlAppliesToListsAndPrescriptions(): void
    {
        $this->assertSame('', ActiveMedicationListService::erxExcludeSql('l.', false));
        $this->assertSame("AND l.erx_uploaded != '1' ", ActiveMedicationListService::erxExcludeSql('l.', true));
        $this->assertSame("AND erx_uploaded != '1' ", ActiveMedicationListService::erxExcludeSql('', true));
    }

    /**
     * Print uses the card patient, not whatever the session later became.
     */
    public function testRequestedPatientIdPrefersTheQueryPid(): void
    {
        $this->assertSame(7, ActiveMedicationListService::requestedPatientId('7', '99'));
        $this->assertSame(7, ActiveMedicationListService::requestedPatientId(7, 99));
        $this->assertSame(99, ActiveMedicationListService::requestedPatientId(null, '99'));
        $this->assertSame(99, ActiveMedicationListService::requestedPatientId('7.5', '99'));
        $this->assertSame(99, ActiveMedicationListService::requestedPatientId('1e3', 99));
        $this->assertSame(0, ActiveMedicationListService::requestedPatientId('0', 'nope'));
        $this->assertSame(
            '/interface/patient_file/summary/active_medications_print.php?pid=7',
            ActiveMedicationListService::printHref('', 7)
        );
    }

    /**
     * Print uses the session chart when one exists; otherwise a validated query pid.
     */
    public function testPrintPatientIdStaysOnTheSessionChart(): void
    {
        $this->assertSame(7, ActiveMedicationListService::printPatientId('7', '7'));
        $this->assertSame(7, ActiveMedicationListService::printPatientId(null, '7'));
        $this->assertSame(0, ActiveMedicationListService::printPatientId('99', '7'));
        $this->assertSame(7, ActiveMedicationListService::printPatientId('7.5', '7'));
        $this->assertSame(7, ActiveMedicationListService::printPatientId('7', '0'));
        $this->assertSame(0, ActiveMedicationListService::printPatientId(null, '0'));
        $this->assertSame(0, ActiveMedicationListService::printPatientId('7.5', '0'));
    }

    /**
     * A second issue with the same name is not a second row.
     */
    public function testMergeSkipsADuplicateIssueName(): void
    {
        $rows = ActiveMedicationListService::merge(
            [
                ['title' => 'Aspirin', 'begdate' => '2024-01-01'],
                ['title' => 'aspirin', 'begdate' => '2024-02-01'],
                ['title' => 12],
            ],
            [
                ['drug' => 'Aspirin', 'dosage' => '81 mg'],
                ['drug' => '  ', 'dosage' => 'skip'],
            ]
        );
        $this->assertCount(1, $rows);
        $this->assertSame('issue', $rows[0]['source']);
        $this->assertSame('aspirin', $rows[0]['title']);
        $this->assertSame('2024-02-01', $rows[0]['start']);
    }

    /**
     * The later start date is the dose that stays.
     */
    public function testMergeKeepsTheLaterDose(): void
    {
        $rows = ActiveMedicationListService::merge(
            [
                [
                    'title' => 'Lisinopril',
                    'drug_dosage_instructions' => '5 mg daily',
                    'begdate' => '2024-01-01',
                ],
                [
                    'title' => 'Lisinopril',
                    'drug_dosage_instructions' => '10 mg daily',
                    'begdate' => '2026-03-01',
                ],
            ],
            [
                [
                    'drug' => 'Lisinopril',
                    'dosage' => '20 mg',
                    'start_date' => '2025-06-01',
                ],
            ]
        );
        $this->assertCount(1, $rows);
        $this->assertSame('issue', $rows[0]['source']);
        $this->assertSame('10 mg daily', $rows[0]['dose']);
        $this->assertSame('2026-03-01', $rows[0]['start']);
    }

    /**
     * A newer prescription replaces an older issue with the same name.
     */
    public function testMergeKeepsANewerPrescriptionDose(): void
    {
        $rows = ActiveMedicationListService::merge(
            [
                [
                    'title' => 'Metformin',
                    'drug_dosage_instructions' => '500 mg daily',
                    'begdate' => '2024-01-01',
                ],
            ],
            [
                [
                    'drug' => 'Metformin',
                    'dosage' => '1000 mg',
                    'start_date' => '2026-04-01',
                ],
            ]
        );
        $this->assertCount(1, $rows);
        $this->assertSame('prescription', $rows[0]['source']);
        $this->assertSame('1000 mg', $rows[0]['dose']);
        $this->assertSame('2026-04-01', $rows[0]['start']);
    }

    /**
     * A prescription dose can be only the strength or only the instructions.
     */
    public function testMergeKeepsASingleDosePart(): void
    {
        $rows = ActiveMedicationListService::merge(
            [],
            [
                ['drug' => 'Metformin', 'dosage' => '500 mg', 'drug_dosage_instructions' => ''],
            ]
        );
        $this->assertSame('500 mg', $rows[0]['dose']);
    }

    /**
     * Names already listed, and blank titles, stay off the inactive list.
     */
    public function testExcludeListedNamesDropsBlankTitles(): void
    {
        $rows = ActiveMedicationListService::excludeListedNames(
            [
                ['source' => 'issue', 'title' => '  ', 'dose' => '', 'start' => null, 'end' => null, 'comments' => ''],
                ['source' => 'issue', 'title' => 'Aspirin', 'dose' => '', 'start' => null, 'end' => null, 'comments' => ''],
            ],
            [
                ['title' => ''],
                ['title' => 'Aspirin'],
            ]
        );
        $this->assertSame([], $rows);
    }

    /**
     * A missing patient id has no query string. A web root keeps a single slash.
     */
    public function testPrintHrefOmitsPidWhenNoneIsSelected(): void
    {
        $this->assertSame(
            '/openemr/interface/patient_file/summary/active_medications_print.php',
            ActiveMedicationListService::printHref('/openemr/', 0)
        );
    }

    /**
     * The heading uses only the name columns that are non-empty strings.
     */
    public function testPatientNameSkipsBlankAndNonStringColumns(): void
    {
        $this->assertSame(
            'Ada Lovelace',
            ActiveMedicationListService::patientName([
                'title' => ' ',
                'fname' => 'Ada',
                'mname' => 3,
                'lname' => 'Lovelace',
            ])
        );
        $this->assertSame('', ActiveMedicationListService::patientName([]));
    }

    /**
     * A null date is blank. A formatter that does not return a string is blank.
     */
    public function testFormatListDateRejectsNullAndNonStringResults(): void
    {
        $this->assertSame('', ActiveMedicationListService::formatListDate(null, static fn (string $v): string => $v));
        $this->assertSame('', ActiveMedicationListService::formatListDate('2024-01-02', static fn (string $v): int => 1));
        $this->assertSame('01/02/2024', ActiveMedicationListService::formatListDate('2024-01-02', static fn (string $v): string => '01/02/2024'));
    }

    /**
     * An empty section says none. A filled section escapes the cell text.
     */
    public function testSectionHtmlCoversEmptyAndFilledLists(): void
    {
        $translate = static fn (string $s): string => $s;
        $escape = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES);
        $dates = static fn (?string $d): string => $d ?? '';
        $empty = ActiveMedicationListService::sectionHtml('Active', [], $translate, $escape, $dates, 'table');
        $this->assertStringContainsString('>Active<', $empty);
        $this->assertStringContainsString('None{{Issues}}', $empty);
        $this->assertStringNotContainsString('<table', $empty);

        $filled = ActiveMedicationListService::sectionHtml(
            'Inactive',
            [[
                'title' => 'A < B',
                'dose' => '10 mg',
                'start' => '2024-01-01',
                'end' => null,
                'comments' => 'note',
            ]],
            $translate,
            $escape,
            $dates,
            'table table-sm'
        );
        $this->assertStringContainsString('A &lt; B', $filled);
        $this->assertStringContainsString('>Medication<', $filled);
        $this->assertStringContainsString('class="table table-sm"', $filled);
        $this->assertStringContainsString('>note<', $filled);
    }

    public function testMergeKeepsTheIssueWhenTheStartMatches(): void
    {
        $rows = ActiveMedicationListService::merge(
            [
                [
                    'title' => 'Metformin',
                    'drug_dosage_instructions' => '500 mg daily',
                    'begdate' => '2024-01-01',
                ],
                [
                    'title' => 'Aspirin',
                    'drug_dosage_instructions' => '81 mg daily',
                ],
            ],
            [
                [
                    'drug' => 'Metformin',
                    'dosage' => '1000 mg',
                    'start_date' => '2024-01-01',
                ],
                [
                    'drug' => 'Aspirin',
                    'dosage' => '325 mg',
                ],
            ]
        );
        $this->assertCount(2, $rows);
        $this->assertSame('issue', $rows[0]['source']);
        $this->assertSame('500 mg daily', $rows[0]['dose']);
        $this->assertSame('issue', $rows[1]['source']);
        $this->assertSame('81 mg daily', $rows[1]['dose']);
    }
}
