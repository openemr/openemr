<?php

/**
 * Database-backed test: a user token's SMART launch context binds the launched
 * patient through the real user and patient lookups.
 *
 * Only the access-token read is replaced; the user lookup, the patient lookup
 * and the patients/demo ACL check run against the database. The isolated tests
 * override checkUserHasAccessToPatient() or findPatientByUuid(), which is why a
 * string patient UUID reaching the binary patient_data.uuid column went
 * unnoticed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\RestControllers\Authorization;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;

final class PopulateTokenContextPatientBindingTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private string $patientUuid;
    private string $userUuid;
    private int $userId;
    /** True when the test gave the admin user a uuid, so tearDown clears it again. */
    private bool $assignedUserUuid = false;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();

        $patientUuidBytes = QueryUtils::fetchSingleValue(
            'SELECT `uuid` FROM `patient_data` WHERE `pubpid` LIKE ? ORDER BY `pid` LIMIT 1',
            'uuid',
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . '%']
        );
        if (!is_string($patientUuidBytes) || $patientUuidBytes === '') {
            self::fail('patient fixture with a uuid not found');
        }
        $this->patientUuid = UuidRegistry::uuidToString($patientUuidBytes);

        $user = QueryUtils::querySingleRow('SELECT `id`, `uuid` FROM `users` WHERE `username` = ?', ['admin']);
        if (!is_array($user) || !is_numeric($user['id'] ?? null)) {
            self::fail('admin user not found');
        }
        $this->userId = (int) $user['id'];
        $userUuidBytes = $user['uuid'] ?? null;
        if (!is_string($userUuidBytes) || $userUuidBytes === '') {
            // A fresh install leaves the uuid unset until something needs it.
            $userUuidBytes = (new UuidRegistry(['table_name' => 'users']))->createUuid();
            QueryUtils::sqlStatementThrowException('UPDATE `users` SET `uuid` = ? WHERE `id` = ?', [$userUuidBytes, $this->userId]);
            $this->assignedUserUuid = true;
        }
        $this->userUuid = UuidRegistry::uuidToString($userUuidBytes);
    }

    protected function tearDown(): void
    {
        if ($this->assignedUserUuid) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `uuid_registry` WHERE `table_name` = ? AND `uuid` = ?',
                ['users', UuidRegistry::uuidToBytes($this->userUuid)]
            );
            QueryUtils::sqlStatementThrowException('UPDATE `users` SET `uuid` = NULL WHERE `id` = ?', [$this->userId]);
        }
        $this->fixtureManager->removePatientFixtures();
    }

    public function testLaunchedPatientIsBoundForAUserToken(): void
    {
        $request = $this->requestForAdmin();

        $this->strategyWithContext(['patient' => $this->patientUuid])->populateTokenContextForRequest($request);

        $this->assertSame($this->patientUuid, $request->getPatientUUIDString());
    }

    public function testUnknownPatientIsNotBound(): void
    {
        $request = $this->requestForAdmin();
        $unknownPatientUuid = UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid());

        $this->strategyWithContext(['patient' => $unknownPatientUuid])->populateTokenContextForRequest($request);

        $this->assertNull($request->getPatientUUIDString());
    }

    private function requestForAdmin(): HttpRestRequest
    {
        $request = HttpRestRequest::create('/apis/default/fhir/Patient');
        $request->setRequestUser($this->userUuid, ['id' => $this->userId, 'username' => 'admin']);
        return $request;
    }

    /**
     * @param array<string, string> $context
     */
    private function strategyWithContext(array $context): BearerTokenAuthorizationStrategy
    {
        return new class ($context, new OEGlobalsBag(), $this->createMock(EventAuditLogger::class)) extends BearerTokenAuthorizationStrategy {
            /**
             * @param array<string, string> $context
             */
            public function __construct(private readonly array $context, OEGlobalsBag $globalsBag, EventAuditLogger $auditLogger)
            {
                parent::__construct($globalsBag, $auditLogger);
            }

            /**
             * @return array<string, string>
             */
            public function getTokenContextForRequest(HttpRestRequest $restRequest): array
            {
                return $this->context;
            }
        };
    }
}
