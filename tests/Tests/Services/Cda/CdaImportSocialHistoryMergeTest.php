<?php

/**
 * A document for a patient imported before, who has no encounters yet, is merged into that patient. Its smoking
 * status must go to them, not to the pid a new patient would have had: the patient_data entry that decides which
 * patient it is comes last in the import.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Cda;

use Carecoordination\Model\CarecoordinationTable;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class CdaImportSocialHistoryMergeTest extends TestCase
{
    private const REFERRER = 'test-fixture-merge-referrer';

    private int $pid;

    protected function setUp(): void
    {
        $next = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data", 'next_pid');
        $this->assertIsNumeric($next);
        $this->pid = (int) $next;
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, referrerID, `date`) VALUES (?, ?, 'test-fixture-merge', 'test-fixture-merge', ?, NOW())",
            [$this->pid, random_bytes(16), self::REFERRER]
        );
    }

    protected function tearDown(): void
    {
        foreach ([$this->pid, $this->pid + 1] as $pid) {
            CdaSocialHistoryWriterTest::removeHistory($pid);
        }
        QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid IN (?, ?)", [$this->pid, $this->pid + 1]);
        $left = QueryUtils::fetchSingleValue(
            "SELECT (SELECT COUNT(*) FROM history_data WHERE pid IN (?, ?)) + (SELECT COUNT(*) FROM patient_data WHERE pid IN (?, ?)) AS n",
            'n',
            [$this->pid, $this->pid + 1, $this->pid, $this->pid + 1]
        );
        $this->assertEquals(0, $left, 'the synthetic rows are removed');
    }

    public function testASmokingStatusGoesToThePatientTheDocumentIsMergedInto(): void
    {
        $table = new CarecoordinationTable();
        // As the parser leaves it: social history read before the patient's own entry.
        (new ReflectionProperty(CarecoordinationTable::class, 'documentData'))->setValue($table, [
            'field_name_value_array' => [
                'social_history' => [1 => ['extension' => 'merge-history', 'smoking' => 'Former smoker|8517006|20240121']],
                'patient_data' => [1 => ['fname' => 'test-fixture-merge', 'lname' => 'test-fixture-merge', 'referrerID' => self::REFERRER]],
            ],
        ]);

        $table->insert_patient(0, 0);

        $merged = QueryUtils::fetchSingleValue("SELECT tobacco FROM history_data WHERE pid = ? ORDER BY id DESC LIMIT 1", 'tobacco', [$this->pid]);
        $this->assertIsString($merged, 'the patient imported before has the smoking status');
        $this->assertStringContainsString('quittobacco', $merged);
        $this->assertEquals(
            0,
            QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM history_data WHERE pid = ?", 'n', [$this->pid + 1]),
            'nothing is written for the pid a new patient would have had'
        );
    }
}
