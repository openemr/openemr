<?php

/**
 * Regression coverage for how a merge folds lists_touch rows.
 *
 * lists_touch is keyed on (pid, type), so each chart holds at most one row per list type and a merge
 * has to reconcile them type by type. This test exists because the original implementation shared a
 * single result-set cursor across its nested loops: the first source row consumed every target row,
 * so any second type was never compared and fell through to the bulk repoint at the end -- which
 * collides with the target's own row on the primary key.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Patient;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Patient\DuplicatePatientService;
use OpenEMR\Services\Patient\PatientMergeRequest;
use OpenEMR\Services\Patient\PatientMergeService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class PatientMergeListsTouchTest extends TestCase
{
    /** @var list<int> */
    private array $createdPids = [];

    protected function tearDown(): void
    {
        foreach ($this->createdPids as $pid) {
            $row = QueryUtils::querySingleRow("SELECT uuid FROM patient_data WHERE pid = ?", [$pid]);
            if (is_array($row) && isset($row['uuid'])) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM uuid_registry WHERE table_name = 'patient_data' AND uuid = ?",
                    [$row['uuid']]
                );
            }
            QueryUtils::sqlStatementThrowException("DELETE FROM lists_touch WHERE pid = ?", [$pid]);
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$pid]);
        }
        $this->createdPids = [];
    }

    /**
     * Both charts carry the same two list types, and the source's rows are the older ones. Every
     * type must end up on the target carrying the source's date, with no row left behind.
     *
     * Before the fix this threw: only the first type was reconciled, and the leftover source row
     * was then repointed onto a (pid, type) the target already occupied.
     */
    #[Test]
    public function foldsEveryListTypeNotJustTheFirst(): void
    {
        $targetPid = $this->createPatient();
        $sourcePid = $this->createPatient();

        $this->addListsTouch($targetPid, 'allergy', '2020-06-01 00:00:00');
        $this->addListsTouch($targetPid, 'medication', '2020-06-01 00:00:00');
        $this->addListsTouch($sourcePid, 'allergy', '2019-03-01 00:00:00');
        $this->addListsTouch($sourcePid, 'medication', '2019-03-01 00:00:00');

        $result = $this->mergeService()->merge(new PatientMergeRequest(
            targetPid: $targetPid,
            sourcePid: $sourcePid,
            skipIdentityChecks: true,
        ));

        self::assertTrue(
            $result->successful,
            'merge reported: ' . ($result->errorMessage ?? '')
        );

        $rows = QueryUtils::fetchRecords(
            "SELECT `type`, `date` FROM lists_touch WHERE pid = ? ORDER BY `type`",
            [$targetPid]
        );
        $dates = self::datesByType($rows);

        self::assertSame(
            ['allergy', 'medication'],
            array_keys($dates),
            'both list types should survive on the target chart, exactly once each'
        );
        self::assertSame('2019-03-01 00:00:00', $dates['allergy'], 'the older source row should win');
        self::assertSame('2019-03-01 00:00:00', $dates['medication'], 'the second type must be folded too');

        self::assertSame(
            [],
            QueryUtils::fetchRecords("SELECT `type` FROM lists_touch WHERE pid = ?", [$sourcePid]),
            'no source rows should be left behind'
        );
    }

    /**
     * When the target already holds the older row, the source's is discarded rather than promoted --
     * again for every type, not just the first.
     */
    #[Test]
    public function discardsNewerSourceRowsForEveryType(): void
    {
        $targetPid = $this->createPatient();
        $sourcePid = $this->createPatient();

        $this->addListsTouch($targetPid, 'allergy', '2018-01-01 00:00:00');
        $this->addListsTouch($targetPid, 'medication', '2018-01-01 00:00:00');
        $this->addListsTouch($sourcePid, 'allergy', '2021-09-01 00:00:00');
        $this->addListsTouch($sourcePid, 'medication', '2021-09-01 00:00:00');

        $result = $this->mergeService()->merge(new PatientMergeRequest(
            targetPid: $targetPid,
            sourcePid: $sourcePid,
            skipIdentityChecks: true,
        ));

        self::assertTrue($result->successful, 'merge reported: ' . ($result->errorMessage ?? ''));

        $rows = QueryUtils::fetchRecords(
            "SELECT `type`, `date` FROM lists_touch WHERE pid = ? ORDER BY `type`",
            [$targetPid]
        );
        $dates = self::datesByType($rows);

        self::assertSame(['allergy', 'medication'], array_keys($dates));
        self::assertSame('2018-01-01 00:00:00', $dates['allergy'], 'the target keeps its older row');
        self::assertSame('2018-01-01 00:00:00', $dates['medication'], 'and does so for every type');
    }

    /**
     * Collapse the queried rows into type => date, narrowing the mixed cells a row set carries.
     *
     * @param list<array<mixed>> $rows
     *
     * @return array<string, string>
     */
    private static function datesByType(array $rows): array
    {
        $dates = [];
        foreach ($rows as $row) {
            $type = $row['type'] ?? null;
            $date = $row['date'] ?? null;
            self::assertIsString($type);
            self::assertIsString($date);
            $dates[$type] = $date;
        }

        return $dates;
    }

    private function mergeService(): PatientMergeService
    {
        return new PatientMergeService(
            EventAuditLogger::getInstance(),
            new Session(new MockArraySessionStorage()),
            new NullLogger(),
            new DuplicatePatientService(ServiceContainer::getClock()),
            sys_get_temp_dir() . '/openemr-merge-test-documents',
        );
    }

    private function addListsTouch(int $pid, string $type, string $date): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO lists_touch (pid, `type`, `date`) VALUES (?, ?, ?)",
            [$pid, $type, $date]
        );
    }

    private function createPatient(): int
    {
        $result = QueryUtils::querySingleRow("SELECT IFNULL(MAX(pid), 0) + 1 AS next_pid FROM patient_data");
        self::assertIsArray($result);
        self::assertIsNumeric($result['next_pid'] ?? null);
        $pid = (int) $result['next_pid'];

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, pubpid, fname, lname, DOB, sex, ss, dupscore)"
            . " VALUES (?, ?, ?, 'ListsTouch', 'MergeTest', '1975-04-04', 'Female', '123-45-6789', ?)",
            [
                $pid,
                (new UuidRegistry(['table_name' => 'patient_data']))->createUuid(),
                'test-merge-' . uniqid(),
                DuplicatePatientService::SCORE_UNIQUE,
            ]
        );

        $this->createdPids[] = $pid;
        return $pid;
    }
}
