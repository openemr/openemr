<?php

/**
 * The fee sheet's Justify screen links each diagnosis's problem to the visit, creating the problem when
 * "create problem" is checked. A created problem was linked with the visit and the problem swapped.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Forms\FeeSheet\Review\CodeInfo;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../interface/forms/fee_sheet/review/fee_sheet_queries.php';

class FeeSheetReviewIssueLinkTest extends TestCase
{
    private const PID = 990000311;
    private const ENCOUNTER = 990000312;
    /** Not a real ICD-10 code. */
    private const CODE = 'ZZ9.311';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'test-fixture-link', 'test-fixture-link', NOW())",
            [self::PID, random_bytes(16)]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_encounter (pid, encounter, uuid, `date`) VALUES (?, ?, ?, '2026-03-02 10:00:00')",
            [self::PID, self::ENCOUNTER, random_bytes(16)]
        );
    }

    protected function tearDown(): void
    {
        $uuids = QueryUtils::fetchTableColumn(
            "SELECT uuid FROM lists WHERE pid = ? AND uuid IS NOT NULL UNION SELECT uuid FROM issue_encounter WHERE pid = ? AND uuid IS NOT NULL",
            'uuid',
            [self::PID, self::PID]
        );
        $this->cleanUp();
        $left = QueryUtils::fetchSingleValue(
            "SELECT (SELECT COUNT(*) FROM patient_data WHERE pid = ?) + (SELECT COUNT(*) FROM form_encounter WHERE pid = ?)"
            . " + (SELECT COUNT(*) FROM lists WHERE pid = ?) + (SELECT COUNT(*) FROM issue_encounter WHERE pid = ?)"
            . " + (SELECT COUNT(*) FROM lists_touch WHERE pid = ?) AS n",
            'n',
            [self::PID, self::PID, self::PID, self::PID, self::PID]
        );
        $this->assertEquals(0, $left, 'the synthetic rows are removed');
        foreach ($uuids as $uuid) {
            $registered = QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM uuid_registry WHERE uuid = ?", 'n', [$uuid]);
            $this->assertEquals(0, $registered, 'the synthetic rows\' uuids are unregistered');
        }
        parent::tearDown();
    }

    public function testACreatedProblemIsLinkedToTheVisit(): void
    {
        update_issues(self::PID, self::ENCOUNTER, [$this->diagnosisToCreate()]);

        $this->assertSame([['list_id' => $this->problemId(), 'encounter' => self::ENCOUNTER]], $this->links());
    }

    public function testSavingTheSameDiagnosisAgainKeepsOneLink(): void
    {
        update_issues(self::PID, self::ENCOUNTER, [$this->diagnosisToCreate()]);
        // The second save finds the problem the first one created.
        update_issues(self::PID, self::ENCOUNTER, [$this->diagnosisToCreate()]);

        $this->assertSame([['list_id' => $this->problemId(), 'encounter' => self::ENCOUNTER]], $this->links());
    }

    public function testAProblemAlreadyOnTheListIsLinkedToTheVisit(): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO lists (`date`, begdate, type, pid, diagnosis, title) VALUES (NOW(), '2026-01-05', 'medical_problem', ?, ?, 'test-fixture-link')",
            [self::PID, 'ICD10:' . self::CODE]
        );

        update_issues(self::PID, self::ENCOUNTER, [$this->diagnosisToCreate()]);

        $this->assertSame([['list_id' => $this->problemId(), 'encounter' => self::ENCOUNTER]], $this->links());
    }

    /**
     * A diagnosis as the Justify screen posts it with "create problem" checked.
     */
    private function diagnosisToCreate(): CodeInfo
    {
        $diagnosis = new CodeInfo(self::CODE, 'ICD10', 'test-fixture-link problem');
        $this->assertSame('TRUE', $diagnosis->allowed_to_create_problem_from_diagnosis, 'ICD10 may create problems');
        $diagnosis->db_id = null;
        $diagnosis->create_problem = true;
        return $diagnosis;
    }

    private function problemId(): int
    {
        $ids = QueryUtils::fetchTableColumn("SELECT id FROM lists WHERE pid = ? AND diagnosis = ?", 'id', [self::PID, 'ICD10:' . self::CODE]);
        $this->assertCount(1, $ids, 'the patient has one problem for the diagnosis');
        $this->assertIsNumeric($ids[0]);
        return (int) $ids[0];
    }

    /**
     * @return list<array{list_id: int, encounter: int}>
     */
    private function links(): array
    {
        $links = [];
        foreach (QueryUtils::fetchRecords("SELECT list_id, encounter FROM issue_encounter WHERE pid = ? ORDER BY id", [self::PID]) as $row) {
            $this->assertIsNumeric($row['list_id']);
            $this->assertIsNumeric($row['encounter']);
            $links[] = ['list_id' => (int) $row['list_id'], 'encounter' => (int) $row['encounter']];
        }
        return $links;
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM issue_encounter WHERE pid = ?)", [self::PID]);
        QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM lists WHERE pid = ?)", [self::PID]);
        foreach (['issue_encounter' => 'pid', 'lists' => 'pid', 'lists_touch' => 'pid', 'form_encounter' => 'pid', 'patient_data' => 'pid'] as $table => $column) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `$table` WHERE `$column` = ?", [self::PID]);
        }
    }
}
