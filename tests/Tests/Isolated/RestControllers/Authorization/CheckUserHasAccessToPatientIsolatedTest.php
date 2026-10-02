<?php

/**
 * Isolated tests for BearerTokenAuthorizationStrategy::checkUserHasAccessToPatient().
 *
 * Coverage for the SMART launch-context patient binding. The check must return
 * false on any missing / malformed input and honour OpenEMR's core UI
 * enforcement model (base `patients/demo` ACL plus optional per-patient
 * `squad` ACL).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Authorization;

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

class CheckUserHasAccessToPatientIsolatedTest extends TestCase
{
    private const VALID_USER_UUID = '11111111-1111-4111-a111-111111111111';
    private const VALID_PATIENT_UUID = '22222222-2222-4222-a222-222222222222';
    // Second patient UUID used to simulate a SMART patient-picker path in
    // which the token context claims a patient the caller has no relation to.
    private const OTHER_PATIENT_UUID = '33333333-3333-4333-a333-333333333333';

    /**
     * Build a strategy subclass that:
     *   - takes pre-canned user / patient records and an ACL verdict
     *   - short-circuits the AclMain::aclCheckCore call (isolated tests do not
     *     stand up the gACL database) via the aclCheckUserPatientAccess seam
     *   - records the ACL check arguments so tests can assert them
     *
     * @param array<string,mixed>|null $userRecord     Row returned from UserService::getUserByUUID (null = "not found").
     * @param array<string,mixed>|null $patientRecord  Row returned by findPatientByUuid (null = "not found").
     * @param bool                     $aclVerdict     What aclCheckUserPatientAccess should return.
     * @param array{username: string, squad: string}|null &$aclCallCapture   Populated with the ACL call arguments if reached.
     * @param-out array{username: string, squad: string}|null $aclCallCapture
     */
    private function makeStrategy(
        ?array $userRecord,
        ?array $patientRecord,
        bool $aclVerdict,
        ?array &$aclCallCapture = null
    ): BearerTokenAuthorizationStrategy {
        $aclCallCapture = null;

        $userService = $this->createMock(UserService::class);
        $userService->method('getUserByUUID')->willReturn($userRecord ?? false);

        // NOTE: we deliberately do NOT create a PatientService mock. Its
        // constructor loads `custom/code_types.inc.php` which requires a live
        // database. Since the subclass below overrides `findPatientByUuid`,
        // PatientService is never touched in this test.

        $auditLogger = $this->createMock(EventAuditLogger::class);

        $strategy = new class ($auditLogger, $patientRecord, $aclVerdict, $aclCallCapture) extends BearerTokenAuthorizationStrategy {
            /**
             * @param array<string,mixed>|null $patientRecord
             * @param array{username: string, squad: string}|null &$aclCallCaptureRef
             */
            public function __construct(
                EventAuditLogger $auditLogger,
                private readonly ?array $patientRecord,
                private readonly bool $aclVerdict,
                /**
                 * By-ref writes here surface in the enclosing test scope; PHPStan's
                 * "never read" check does not model the reference back-channel.
                 * @phpstan-ignore property.onlyWritten
                 */
                private ?array &$aclCallCaptureRef,
            ) {
                parent::__construct(new OEGlobalsBag(), $auditLogger);
            }

            protected function findPatientByUuid(string $patientUuid): ?array
            {
                return $this->patientRecord;
            }

            protected function aclCheckUserPatientAccess(string $username, string $squad): bool
            {
                $this->aclCallCaptureRef = ['username' => $username, 'squad' => $squad];
                return $this->aclVerdict;
            }
        };

        $strategy->setUserService($userService);
        // PatientService is intentionally not set — findPatientByUuid is overridden.
        // Inject a null-logger so the rejection logging paths do not attempt
        // to write anywhere real.
        $strategy->setLogger($this->createMock(LoggerInterface::class));

        return $strategy;
    }

    /**
     * Invoke the protected checkUserHasAccessToPatient() method under test.
     */
    private function invokeCheck(BearerTokenAuthorizationStrategy $strategy, mixed $userId, mixed $patientUuid): bool
    {
        $method = new ReflectionMethod(BearerTokenAuthorizationStrategy::class, 'checkUserHasAccessToPatient');
        /** @var bool $result */
        $result = $method->invoke($strategy, $userId, $patientUuid);
        return $result;
    }

    // -------------------------------------------------------------------------
    // Positive paths
    // -------------------------------------------------------------------------

    public function testAllowsUserWithBasePatientAclAndNoSquad(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 1, 'username' => 'clinician1'],
            patientRecord: ['pid' => 42, 'squad' => ''],
            aclVerdict: true,
            aclCallCapture: $aclCall
        );

        $this->assertTrue($this->invokeCheck($strategy, self::VALID_USER_UUID, self::VALID_PATIENT_UUID));
        $this->assertSame(['username' => 'clinician1', 'squad' => ''], $aclCall);
    }

    public function testAllowsUserWithBasePatientAclAndMatchingSquadAcl(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 7, 'username' => 'squad_member'],
            patientRecord: ['pid' => 99, 'squad' => 'blue'],
            aclVerdict: true,
            aclCallCapture: $aclCall
        );

        $this->assertTrue($this->invokeCheck($strategy, self::VALID_USER_UUID, self::VALID_PATIENT_UUID));
        $this->assertSame(['username' => 'squad_member', 'squad' => 'blue'], $aclCall);
    }

    // -------------------------------------------------------------------------
    // ACL denials (base + squad)
    // -------------------------------------------------------------------------

    public function testDeniesUserWithoutBasePatientsDemoAcl(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 2, 'username' => 'nobody'],
            patientRecord: ['pid' => 42, 'squad' => ''],
            aclVerdict: false, // AclMain::aclCheckCore returns false
            aclCallCapture: $aclCall
        );

        $this->assertFalse($this->invokeCheck($strategy, self::VALID_USER_UUID, self::VALID_PATIENT_UUID));
        $this->assertSame(['username' => 'nobody', 'squad' => ''], $aclCall);
    }

    public function testDeniesUserWithoutMatchingSquadAcl(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 3, 'username' => 'wrong_squad'],
            patientRecord: ['pid' => 42, 'squad' => 'red'],
            aclVerdict: false, // squad ACL fails
            aclCallCapture: $aclCall
        );

        $this->assertFalse($this->invokeCheck($strategy, self::VALID_USER_UUID, self::VALID_PATIENT_UUID));
        $this->assertSame(['username' => 'wrong_squad', 'squad' => 'red'], $aclCall);
    }

    // -------------------------------------------------------------------------
    // Returns false on unresolved identifiers
    // -------------------------------------------------------------------------

    public function testDeniesWhenPatientUuidDoesNotResolveToARecord(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 4, 'username' => 'clinician2'],
            patientRecord: null, // findPatientByUuid returns null
            aclVerdict: true,
            aclCallCapture: $aclCall
        );

        $this->assertFalse($this->invokeCheck($strategy, self::VALID_USER_UUID, self::OTHER_PATIENT_UUID));
        // ACL check must not be reached when the patient is unresolved.
        $this->assertNull($aclCall);
    }

    public function testDeniesWhenPatientRecordMissingPid(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 5, 'username' => 'clinician3'],
            patientRecord: ['pid' => 0, 'squad' => ''],
            aclVerdict: true,
            aclCallCapture: $aclCall
        );

        $this->assertFalse($this->invokeCheck($strategy, self::VALID_USER_UUID, self::VALID_PATIENT_UUID));
        $this->assertNull($aclCall);
    }

    public function testDeniesWhenUserUuidDoesNotResolveToARecord(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: null, // getUserByUUID returned false
            patientRecord: ['pid' => 42, 'squad' => ''],
            aclVerdict: true,
            aclCallCapture: $aclCall
        );

        $this->assertFalse($this->invokeCheck($strategy, self::VALID_USER_UUID, self::VALID_PATIENT_UUID));
        $this->assertNull($aclCall);
    }

    public function testDeniesWhenUserRecordMissingUsername(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 6], // no username key
            patientRecord: ['pid' => 42, 'squad' => ''],
            aclVerdict: true,
            aclCallCapture: $aclCall
        );

        $this->assertFalse($this->invokeCheck($strategy, self::VALID_USER_UUID, self::VALID_PATIENT_UUID));
        $this->assertNull($aclCall);
    }

    // -------------------------------------------------------------------------
    // Malformed inputs
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function malformedUserIdProvider(): array
    {
        return [
            'null'                => [null],
            'empty string'        => [''],
            'not-a-uuid string'   => ['not-a-uuid'],
            'integer'             => [42],
            'array'               => [['uuid' => self::VALID_USER_UUID]],
        ];
    }

    #[DataProvider('malformedUserIdProvider')]
    public function testDeniesOnMalformedUserId(mixed $userId): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 1, 'username' => 'clinician1'],
            patientRecord: ['pid' => 42, 'squad' => ''],
            aclVerdict: true,
            aclCallCapture: $aclCall
        );

        $this->assertFalse($this->invokeCheck($strategy, $userId, self::VALID_PATIENT_UUID));
        $this->assertNull($aclCall);
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function malformedPatientUuidProvider(): array
    {
        return [
            'null'                => [null],
            'empty string'        => [''],
            'not-a-uuid string'   => ['not-a-uuid'],
            'integer'             => [42],
            'array'               => [['uuid' => self::VALID_PATIENT_UUID]],
        ];
    }

    #[DataProvider('malformedPatientUuidProvider')]
    public function testDeniesOnMalformedPatientUuid(mixed $patientUuid): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 1, 'username' => 'clinician1'],
            patientRecord: ['pid' => 42, 'squad' => ''],
            aclVerdict: true,
            aclCallCapture: $aclCall
        );

        $this->assertFalse($this->invokeCheck($strategy, self::VALID_USER_UUID, $patientUuid));
        $this->assertNull($aclCall);
    }

    // -------------------------------------------------------------------------
    // End-to-end launch-context scenarios
    // -------------------------------------------------------------------------

    /**
     * A `user/*` scoped token is issued to some caller and the token context
     * claims an arbitrary patient UUID the caller has no legitimate
     * relationship with. Previously, `checkUserHasAccessToPatient` returned
     * true unconditionally and SMART launch context was bound to the
     * picked patient. The ACL check now runs, and a user lacking
     * `patients/demo` is denied regardless of what the token context claims.
     */
    public function testDeniesUnauthorizedUserForForeignPatient(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 100, 'username' => 'lowpriv_caller'],
            patientRecord: ['pid' => 5001, 'squad' => ''],
            aclVerdict: false, // caller lacks `patients/demo`
            aclCallCapture: $aclCall
        );

        $this->assertFalse(
            $this->invokeCheck($strategy, self::VALID_USER_UUID, self::OTHER_PATIENT_UUID),
            'user without patients/demo must not be granted patient context'
        );
        $this->assertSame(['username' => 'lowpriv_caller', 'squad' => ''], $aclCall);
    }

    /**
     * A caller walks the SMART patient-picker to a patient UUID they should
     * not be allowed to bind and relies on the stub accepting the picked
     * UUID at launch-context time. A picked patient carrying a squad the
     * caller is not a member of is rejected at launch-context binding,
     * regardless of picker output.
     */
    public function testDeniesPickedPatientWhenSquadAclFails(): void
    {
        $strategy = $this->makeStrategy(
            userRecord: ['id' => 101, 'username' => 'clinician_no_red_squad'],
            patientRecord: ['pid' => 5002, 'squad' => 'red'],
            aclVerdict: false, // squad-ACL denies
            aclCallCapture: $aclCall
        );

        $this->assertFalse(
            $this->invokeCheck($strategy, self::VALID_USER_UUID, self::OTHER_PATIENT_UUID),
            'a picked patient carrying a squad the caller lacks must not bind'
        );
        $this->assertSame(['username' => 'clinician_no_red_squad', 'squad' => 'red'], $aclCall);
    }
}
