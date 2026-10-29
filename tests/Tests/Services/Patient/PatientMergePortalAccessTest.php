<?php

/**
 * Regression coverage for retaining the target chart's portal access during a patient merge.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Shwetank Tomar <53561753+shwetanktomar@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Shwetank Tomar
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

class PatientMergePortalAccessTest extends TestCase
{
    /** @var list<int> */
    private array $createdPids = [];

    /** @var list<string> */
    private array $createdUuids = [];

    protected function tearDown(): void
    {
        foreach ($this->createdPids as $pid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_access_onsite WHERE pid = ?", [$pid]);
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

    #[Test]
    public function keepsTargetPortalCredentialsAndRemovesSourceCredentials(): void
    {
        $targetPid = $this->createPatient();
        $sourcePid = $this->createPatient();
        $targetUsername = 'target-' . uniqid();
        $sourceUsername = 'source-' . uniqid();

        $this->addPortalAccount($targetPid, $targetUsername);
        $this->addPortalAccount($sourcePid, $sourceUsername);

        $result = $this->mergeService()->merge(new PatientMergeRequest(
            targetPid: $targetPid,
            sourcePid: $sourcePid,
            skipIdentityChecks: true,
        ));

        self::assertTrue($result->successful, 'merge reported: ' . ($result->errorMessage ?? ''));

        $targetAccount = QueryUtils::querySingleRow(
            "SELECT portal_username FROM patient_access_onsite WHERE pid = ?",
            [$targetPid]
        );
        self::assertIsArray($targetAccount);
        self::assertSame($targetUsername, $targetAccount['portal_username'] ?? null);
        self::assertSame(
            [],
            QueryUtils::fetchRecords("SELECT id FROM patient_access_onsite WHERE pid = ?", [$sourcePid]),
            'the source portal account must be revoked rather than reassigned to the target'
        );
    }

    #[Test]
    public function preservesSourcePortalCredentialsWhenTargetHasNoAccount(): void
    {
        $targetPid = $this->createPatient();
        $sourcePid = $this->createPatient();
        $sourceUsername = 'source-' . uniqid();
        $this->addPortalAccount($sourcePid, $sourceUsername);

        $result = $this->mergeService()->merge(new PatientMergeRequest(
            targetPid: $targetPid,
            sourcePid: $sourcePid,
            skipIdentityChecks: true,
        ));

        self::assertTrue($result->successful, 'merge reported: ' . ($result->errorMessage ?? ''));

        $targetAccount = QueryUtils::querySingleRow(
            "SELECT portal_username FROM patient_access_onsite WHERE pid = ?",
            [$targetPid]
        );
        self::assertIsArray($targetAccount);
        self::assertSame($sourceUsername, $targetAccount['portal_username'] ?? null);
        self::assertSame(
            [],
            QueryUtils::fetchRecords("SELECT id FROM patient_access_onsite WHERE pid = ?", [$sourcePid]),
            'the source account must be reassigned to the surviving chart'
        );
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

    private function addPortalAccount(int $pid, string $username): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_access_onsite (pid, portal_username, portal_pwd, portal_login_username)"
            . " VALUES (?, ?, 'test-password-hash', ?)",
            [$pid, $username, $username]
        );
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
            . " VALUES (?, ?, ?, 'PortalAccess', 'MergeTest', '1975-04-04', 'Female', '123-45-6789', ?)",
            [
                $pid,
                $uuid,
                'test-portal-merge-' . uniqid(),
                DuplicatePatientService::SCORE_UNIQUE,
            ]
        );

        $this->createdPids[] = $pid;
        $this->createdUuids[] = $uuid;
        return $pid;
    }
}
