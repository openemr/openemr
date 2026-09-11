<?php

/**
 * Merge and de-dupe for the active medication list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

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
        $this->assertSame('BP', $rows[0]['comments']);
        $this->assertSame('prescription', $rows[1]['source']);
        $this->assertSame('Metformin', $rows[1]['title']);
        $this->assertSame('500 mg BID', $rows[1]['dose']);
    }

    /**
     * Blank titles and zero dates do not become rows.
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
    }
}
