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
     * Blank titles and zero dates do not become rows.
     */
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
}
