<?php

/**
 * A patient merge moves the source chart's insurance policies to the target chart.
 *
 * The merge moves the source's visits and claims, and a secondary claim or a rebill for one of them
 * looks up the policy in force on the visit's date. insurance_data is unique on (pid, type, date),
 * so where the target already has a policy at the same level and start date (undated counting as
 * one start), the target's is kept and the source's is deleted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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

class PatientMergeInsuranceTest extends TestCase
{
    /** @var list<int> */
    private array $createdPids = [];

    /**
     * Captured when each patient is created: a merge deletes the source chart, so its uuid can't be
     * looked up in tearDown.
     *
     * @var list<string>
     */
    private array $createdUuids = [];

    protected function tearDown(): void
    {
        foreach ($this->createdPids as $pid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM insurance_data WHERE pid = ?", [$pid]);
            QueryUtils::sqlStatementThrowException("DELETE FROM claims WHERE patient_id = ?", [$pid]);
            QueryUtils::sqlStatementThrowException("DELETE FROM form_encounter WHERE pid = ?", [$pid]);
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$pid]);
        }
        foreach ($this->createdUuids as $uuid) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM uuid_registry WHERE table_name = 'patient_data' AND uuid = ?",
                [$uuid]
            );
        }

        $this->createdPids = [];
        $this->createdUuids = [];
    }

    /**
     * The source's visit was billed to its primary policy. After the merge the visit and its claim
     * are on the target, and so are the policies a secondary claim or a rebill for that visit needs.
     */
    #[Test]
    public function movesTheSourcesPoliciesWithItsBilledVisit(): void
    {
        $targetPid = $this->createPatient();
        $sourcePid = $this->createPatient();

        $this->addPolicy($targetPid, 'primary', '2024-01-01', '9101');
        $this->addPolicy($sourcePid, 'primary', '2022-01-01', '9201');
        $this->addPolicy($sourcePid, 'secondary', '2022-01-01', '9202');
        $encounter = $this->addBilledVisit($sourcePid, '2022-06-15 09:00:00', 9201);

        $this->merge($targetPid, $sourcePid);

        $claim = QueryUtils::querySingleRow(
            "SELECT patient_id, payer_id FROM claims WHERE encounter_id = ?",
            [$encounter]
        );
        self::assertIsArray($claim);
        self::assertIsNumeric($claim['patient_id'] ?? null);
        self::assertIsNumeric($claim['payer_id'] ?? null);
        self::assertSame($targetPid, (int) $claim['patient_id'], 'the claim moves to the target');
        self::assertSame(9201, (int) $claim['payer_id']);

        self::assertSame(
            [
                'primary 2022-01-01 9201',
                'primary 2024-01-01 9101',
                'secondary 2022-01-01 9202',
            ],
            $this->policies($targetPid),
            "the target has its own policy and the source's"
        );
        self::assertSame([], $this->policies($sourcePid), 'no policy is left on the source');

        $primary = getInsuranceDataByDate($targetPid, '2022-06-15', 'primary');
        $secondary = getInsuranceDataByDate($targetPid, '2022-06-15', 'secondary');
        self::assertIsArray($primary, 'a rebill of the visit finds a primary policy');
        self::assertIsArray($secondary, 'a secondary claim for the visit finds a secondary policy');
        self::assertSame('9201', $primary['provider'] ?? null, 'the policy the visit was billed under');
        self::assertSame('9202', $secondary['provider'] ?? null);
    }

    /**
     * Both charts have a primary policy starting 2022-01-01 and an undated primary policy. The
     * target's two are kept and the source's two are deleted; the source's other levels move.
     */
    #[Test]
    public function keepsTheTargetsPolicyAtTheSameLevelAndStart(): void
    {
        $targetPid = $this->createPatient();
        $sourcePid = $this->createPatient();

        $this->addPolicy($targetPid, 'primary', '2022-01-01', '9101');
        $this->addPolicy($targetPid, 'primary', null, '9100');
        $this->addPolicy($sourcePid, 'primary', '2022-01-01', '9201');
        $this->addPolicy($sourcePid, 'primary', null, '9200');
        $this->addPolicy($sourcePid, 'secondary', '2022-01-01', '9202');
        $this->addPolicy($sourcePid, 'tertiary', null, '9203');

        $this->merge($targetPid, $sourcePid);

        $primaries = array_values(array_filter(
            $this->policies($targetPid),
            static fn (string $policy): bool => str_starts_with($policy, 'primary ')
        ));
        self::assertSame(
            ['primary - 9100', 'primary 2022-01-01 9101'],
            $primaries,
            "the target keeps its own primary policies; the source's at the same start are deleted"
        );
        self::assertSame([], $this->policies($sourcePid), 'no policy is left on the source');

        self::assertSame(
            [
                'primary - 9100',
                'primary 2022-01-01 9101',
                'secondary 2022-01-01 9202',
                'tertiary - 9203',
            ],
            $this->policies($targetPid),
            "the source's policies at other levels move to the target"
        );
    }

    private function merge(int $targetPid, int $sourcePid): void
    {
        $service = new PatientMergeService(
            EventAuditLogger::getInstance(),
            new Session(new MockArraySessionStorage()),
            new NullLogger(),
            new DuplicatePatientService(ServiceContainer::getClock()),
            sys_get_temp_dir() . '/openemr-merge-test-documents',
        );

        $result = $service->merge(new PatientMergeRequest(
            targetPid: $targetPid,
            sourcePid: $sourcePid,
            skipIdentityChecks: true,
        ));

        self::assertTrue($result->successful, 'merge reported: ' . ($result->errorMessage ?? ''));
    }

    /**
     * Each policy on the chart as "type date provider", undated as "-", sorted.
     *
     * @return list<string>
     */
    private function policies(int $pid): array
    {
        $policies = [];
        $rows = QueryUtils::fetchRecords("SELECT `type`, `date`, provider FROM insurance_data WHERE pid = ?", [$pid]);
        foreach ($rows as $row) {
            $type = $row['type'] ?? null;
            $date = $row['date'] ?? null;
            $provider = $row['provider'] ?? null;
            self::assertIsString($type);
            self::assertIsString($provider);
            $policies[] = $type . ' ' . (is_string($date) ? $date : '-') . ' ' . $provider;
        }
        sort($policies);

        return $policies;
    }

    private function addPolicy(int $pid, string $type, ?string $date, string $provider): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (pid, `type`, `date`, provider, policy_number, subscriber_relationship)"
            . " VALUES (?, ?, ?, ?, ?, 'self')",
            [$pid, $type, $date, $provider, 'MERGE-' . $provider]
        );
    }

    /**
     * A visit with a reason (a merge treats one without as an import's placeholder) and its claim
     * to the primary payer. Returns the encounter number.
     */
    private function addBilledVisit(int $pid, string $date, int $payerId): int
    {
        $result = QueryUtils::querySingleRow(
            "SELECT IFNULL(MAX(encounter), 0) + 1 AS next_encounter FROM form_encounter"
        );
        self::assertIsArray($result);
        self::assertIsNumeric($result['next_encounter'] ?? null);
        $encounter = (int) $result['next_encounter'];

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_encounter (`date`, reason, pid, encounter, last_level_billed)"
            . " VALUES (?, 'Office visit', ?, ?, 1)",
            [$date, $pid, $encounter]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO claims (patient_id, encounter_id, version, payer_id, status, payer_type, bill_time)"
            . " VALUES (?, ?, 1, ?, 2, 1, ?)",
            [$pid, $encounter, $payerId, $date]
        );

        return $encounter;
    }

    private function createPatient(): int
    {
        $result = QueryUtils::querySingleRow("SELECT IFNULL(MAX(pid), 0) + 1 AS next_pid FROM patient_data");
        self::assertIsArray($result);
        self::assertIsNumeric($result['next_pid'] ?? null);
        $pid = (int) $result['next_pid'];

        $uuid = (new UuidRegistry(['table_name' => 'patient_data']))->createUuid();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, pubpid, fname, lname, DOB, sex, ss, dupscore)"
            . " VALUES (?, ?, ?, 'Insurance', 'MergeTest', '1975-04-04', 'Female', '123-45-6789', ?)",
            [
                $pid,
                $uuid,
                'test-merge-' . uniqid(),
                DuplicatePatientService::SCORE_UNIQUE,
            ]
        );

        $this->createdPids[] = $pid;
        $this->createdUuids[] = $uuid;
        return $pid;
    }
}
