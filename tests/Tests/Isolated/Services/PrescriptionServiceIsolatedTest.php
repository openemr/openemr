<?php

/**
 * PrescriptionServiceIsolatedTest
 *
 * Coverage for the data-layer behavior of the prescription service. The
 * route-level ACL correction (see PrescriptionRouteAclEnforcementIsolatedTest)
 * is one part; two additional gaps sit here:
 *
 *   (a) `PrescriptionService::getAll()` would perform an unfiltered UNION-SELECT
 *       across every patient's prescriptions when no `patient.uuid` filter was
 *       supplied — even a caller with the correct `patients/rx` view ACL could
 *       enumerate the entire tenant, since the ACL is a per-tenant flag not a
 *       per-patient one.
 *
 *   (b) `PrescriptionService::insert()` fed the entire client-supplied payload
 *       into `buildInsertColumns()`, which happily writes any column that
 *       exists on the `prescriptions` table. That let a caller populate
 *       server-managed provenance columns (`created_by`, `provider_id`, etc.)
 *       to anything they wished.
 *
 * The service now returns a validation failure on missing patient binding
 * for (a), and applies an INSERTABLE_FIELDS allowlist to (b) that drops any
 * key the REST contract does not expose. These tests lock both invariants
 * without a live database: the rejection path returns before any query
 * runs, and the allowlist is a class constant we can read via reflection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\PrescriptionService;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[Group('isolated')]
#[Group('security')]
class PrescriptionServiceIsolatedTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // BaseService.php does `require_once(__DIR__ . '/../../custom/code_types.inc.php')`
        // at class load. That file, in turn, executes a `sqlStatement()` at top-level
        // unless `OPENEMR_STATIC_ANALYSIS` is set. Define it before touching the
        // PrescriptionService class so the DB read is skipped.
        if (!defined('OPENEMR_STATIC_ANALYSIS')) {
            define('OPENEMR_STATIC_ANALYSIS', true);
        }
    }

    // -------------------------------------------------------------------------
    // getAll() must return a validation failure when no patient binding is supplied
    // -------------------------------------------------------------------------

    /**
     * Isolated tests cannot construct PrescriptionService normally because
     * its constructor calls `parent::__construct()` which reads the
     * prescriptions table schema, and `UuidRegistry::createMissingUuidsForTables()`
     * which writes uuids into DB rows. Since the rejection branch of
     * `getAll()` returns before any DB access, we can bypass the constructor
     * with reflection and still exercise the branch we care about.
     */
    private function makeService(): PrescriptionService
    {
        return (new ReflectionClass(PrescriptionService::class))->newInstanceWithoutConstructor();
    }

    /**
     * `ProcessingResult::getValidationMessages()` returns `mixed` (untyped
     * legacy signature). Narrow to `array<string, mixed>` at the test boundary
     * so PHPStan is satisfied and the per-key assertions type-check.
     *
     * @return array<string, mixed>
     */
    private function extractValidationMessages(ProcessingResult $result): array
    {
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages, 'ProcessingResult::getValidationMessages() must be an array');
        /** @var array<string, mixed> $messages */
        return $messages;
    }

    public function testGetAllFailsClosedWhenPatientUuidMissing(): void
    {
        $result = $this->makeService()->getAll([]);

        $this->assertFalse($result->isValid(), 'Empty-search getAll() must be a validation failure');
        $messages = $this->extractValidationMessages($result);
        $this->assertArrayHasKey('patient.uuid', $messages);
        $this->assertSame([], $result->getData(), 'No data must be returned when the patient binding is absent');
    }

    public function testGetAllFailsClosedWhenPatientUuidIsEmptyString(): void
    {
        $result = $this->makeService()->getAll(['patient.uuid' => '']);

        $this->assertFalse($result->isValid(), 'Empty-string patient.uuid must also be rejected — sentinel-empty must not enumerate the tenant');
        $this->assertArrayHasKey('patient.uuid', $this->extractValidationMessages($result));
    }

    public function testGetAllFailsClosedWhenOnlyUnrelatedSearchFieldSupplied(): void
    {
        // Callers who accidentally supply `drug=aspirin` alone (no patient
        // binding) must not enumerate every patient prescribed aspirin.
        $result = $this->makeService()->getAll(['drug' => 'aspirin']);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('patient.uuid', $this->extractValidationMessages($result));
    }

    // -------------------------------------------------------------------------
    // insert() field allowlist — INSERTABLE_FIELDS constant properties
    // -------------------------------------------------------------------------

    /**
     * @return list<string>
     */
    private function getInsertableFields(): array
    {
        $reflection = new ReflectionClass(PrescriptionService::class);
        $constants = $reflection->getConstants();
        $this->assertArrayHasKey(
            'INSERTABLE_FIELDS',
            $constants,
            'PrescriptionService::INSERTABLE_FIELDS must exist as the REST-contract field allowlist for insert()'
        );
        /** @var list<string> $insertable */
        $insertable = $constants['INSERTABLE_FIELDS'];
        return $insertable;
    }

    public function testInsertAllowlistIncludesRequiredClinicalFields(): void
    {
        $insertable = $this->getInsertableFields();
        foreach (['patient_id', 'drug', 'dosage', 'quantity', 'provider_id'] as $required) {
            $this->assertContains(
                $required,
                $insertable,
                sprintf('INSERTABLE_FIELDS must contain %s so the documented REST payload continues to work', $required)
            );
        }
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function serverManagedFieldProvider(): array
    {
        // Columns on the `prescriptions` table that must NEVER be populated
        // from client input. These are either provenance fields the server
        // derives, primary keys, or bookkeeping columns whose value would
        // let a caller lie about who wrote the prescription and when.
        return [
            'primary key id'               => ['id'],
            'server-generated uuid'        => ['uuid'],
            'server-managed active'        => ['active'],
            'server-managed date_added'    => ['date_added'],
            'server-managed date_modified' => ['date_modified'],
            'legacy user provenance'       => ['user'],
            'legacy site scoping'          => ['site'],
            'audit created_by'             => ['created_by'],
            'audit updated_by'             => ['updated_by'],
        ];
    }

    #[DataProvider('serverManagedFieldProvider')]
    public function testInsertAllowlistExcludesServerManagedField(string $field): void
    {
        $insertable = $this->getInsertableFields();
        $this->assertNotContains(
            $field,
            $insertable,
            sprintf(
                'INSERTABLE_FIELDS must NOT expose %s — a client that could set this column could set provenance columns or skip audit',
                $field
            )
        );
    }

    public function testInsertAllowlistDoesNotIncludeMagicId(): void
    {
        // Defensive: if a future refactor renames INSERTABLE_FIELDS or lets
        // the primary key sneak into the allowlist, the id-column check on
        // buildInsertColumns is not the only line of defense — assert here
        // so a rename is caught, not just a value edit.
        $insertable = $this->getInsertableFields();
        $this->assertNotContains('id', $insertable);
    }

    // -------------------------------------------------------------------------
    // insert() must enforce per-patient ACL on the supplied patient_id
    //
    // A caller with a tenant-wide `patients / rx write` grant used to be
    // able to create a prescription for ANY patient because insert() never
    // re-checked chart access against the supplied patient_id. Mirrors the
    // DELETE-side ownership assertion.
    // -------------------------------------------------------------------------

    public function testInsertFailsClosedWhenPatientIdMissing(): void
    {
        // Missing patient_id trips the required-field guard rather than the
        // new ACL guard, but the outcome is the same: the row must not land.
        $service = $this->makeService();
        $result = $service->insert(['drug' => 'aspirin']);

        $this->assertFalse($result->isValid());
        $messages = $this->extractValidationMessages($result);
        $this->assertArrayHasKey('patient_id', $messages);
        $this->assertSame([], $result->getData());
    }

    public function testInsertFailsClosedWhenPatientIdIsNonNumeric(): void
    {
        // A caller who supplies a non-numeric patient_id (e.g. injected via
        // JSON coercion) must be rejected before we reach the ACL layer.
        $service = $this->makeService();
        $result = $service->insert([
            'drug' => 'aspirin',
            'patient_id' => 'not-a-pid',
        ]);

        $this->assertFalse($result->isValid());
        $messages = $this->extractValidationMessages($result);
        $this->assertArrayHasKey('patient_id', $messages);
    }

    public function testInsertFailsClosedWhenPatientIdIsZeroOrNegative(): void
    {
        // pid 0 / negatives must never resolve — patient_data pids are
        // strictly positive. Guard covers accidental 0 fallbacks and
        // deliberate underflow attempts.
        foreach ([0, -1, '-42', '0'] as $bad) {
            $result = $this->makeService()->insert([
                'drug' => 'aspirin',
                'patient_id' => $bad,
            ]);

            $this->assertFalse($result->isValid(), sprintf('patient_id=%s must be rejected', var_export($bad, true)));
            $this->assertArrayHasKey('patient_id', $this->extractValidationMessages($result));
        }
    }

    public function testInsertFailsClosedWhenPatientIdDoesNotResolve(): void
    {
        // Simulate `patient_id => 999999` where the pid does not exist.
        // The seam returns null → validation error before any INSERT SQL runs.
        $service = new class extends PrescriptionService {
            public function __construct()
            {
                // Skip parent constructor: it hits the DB.
            }

            protected function findPatientByPid(int $pid): ?array
            {
                return null; // No such patient
            }

            protected function aclCheckUserPatientAccess(string $squad): bool
            {
                // `PHPUnit\Framework\TestCase::fail()` is not accessible from
                // an anonymous non-TestCase subclass, so raise a LogicException
                // instead. If insert() ever calls this seam despite the
                // findPatientByPid → null short-circuit, PHPUnit surfaces the
                // exception as a test error, which the outer assertions catch.
                throw new \LogicException(
                    'ACL check must not run when patient_id does not resolve'
                );
            }
        };

        $result = $service->insert([
            'drug' => 'aspirin',
            'patient_id' => 999999,
        ]);

        $this->assertFalse($result->isValid(), 'Unresolved patient_id must be rejected');
        $messages = $this->extractValidationMessages($result);
        $this->assertArrayHasKey('patient_id', $messages);
        $this->assertSame('Patient does not exist.', $messages['patient_id']);
    }

    public function testInsertRejectsWhenCallerLacksPerPatientAcl(): void
    {
        // A caller has cleared the route (`patients/rx write`) but does
        // NOT have `patients/demo` for the target patient. Previously
        // insert() would happily create the prescription attributed to
        // that patient. Now it rejects.
        $service = new class extends PrescriptionService {
            public bool $aclChecked = false;

            public function __construct()
            {
                // Skip parent constructor: it hits the DB.
            }

            /**
             * Return type narrowed to `array` (parent declares `?array`) so
             * PHPStan does not flag the never-null override as an unused
             * type. PHP covariant returns permit this narrowing.
             *
             * @return array<string,mixed>
             */
            protected function findPatientByPid(int $pid): array
            {
                return ['pid' => $pid, 'squad' => ''];
            }

            protected function aclCheckUserPatientAccess(string $squad): bool
            {
                $this->aclChecked = true;
                return false; // Caller lacks patients/demo for this patient
            }
        };

        $result = $service->insert([
            'drug' => 'aspirin',
            'patient_id' => 42,
            'provider_id' => 1,
        ]);

        $this->assertTrue($service->aclChecked, 'ACL seam must have been invoked');
        $this->assertFalse($result->isValid(), 'Insert must be rejected when caller lacks per-patient ACL');
        $messages = $this->extractValidationMessages($result);
        $this->assertArrayHasKey('patient_id', $messages);
        $this->assertSame('User does not have access to this patient.', $messages['patient_id']);
        $this->assertSame([], $result->getData(), 'No prescription id must be returned on rejection');
    }

    // -------------------------------------------------------------------------
    // getOne() rejects unknown / malformed prescription UUIDs with a
    // validation-error ProcessingResult so RestControllerHelper maps to 400
    // (matches the historical PatientValidator::validateId shape).
    //
    // Per-patient authorization is applied at the boundary (SMART token
    // check for FHIR-path callers; route-level `patients/rx view` for the
    // REST path) — not duplicated in the service. See the
    // BearerTokenAuthorizationStrategy::checkUserHasAccessToPatient docs.
    // -------------------------------------------------------------------------

    public function testGetOneReturnsValidationErrorWhenPrescriptionHasNoOwner(): void
    {
        // Orphaned patient_id (patient row deleted) or unknown prescription
        // uuid. getOne returns a validation-error ProcessingResult keyed on
        // `uuid` so RestControllerHelper maps to 400 (matches the shape
        // PrescriptionApiTest::testGetOneNotFound pins).
        $service = new class extends PrescriptionService {
            public function __construct()
            {
                // Skip parent constructor: it hits the DB.
            }

            public function findPatientForPrescription(string $prescriptionUuid): ?array
            {
                return null;
            }

            protected function aclCheckUserPatientAccess(string $squad): bool
            {
                throw new \LogicException(
                    'ACL check must not run when the prescription has no resolvable owner'
                );
            }
        };
        $result = $service->getOne('11111111-2222-3333-4444-555555555555');

        $this->assertFalse($result->isValid(), 'getOne on unresolved prescription must be a validation failure (mapped to 400 by the controller)');
        $this->assertSame([], $result->getData(), 'No data may be returned when the prescription cannot be resolved');
        $messages = $this->extractValidationMessages($result);
        $this->assertArrayHasKey('uuid', $messages);
    }

    // -------------------------------------------------------------------------
    // delete() rejects unknown / malformed prescription UUIDs with a
    // validation-error ProcessingResult (matches the getOne shape).
    // -------------------------------------------------------------------------

    public function testDeleteRejectsWhenPrescriptionHasNoOwner(): void
    {
        $service = new class extends PrescriptionService {
            public function __construct()
            {
                // Skip parent constructor: it hits the DB.
            }

            public function findPatientForPrescription(string $prescriptionUuid): ?array
            {
                return null;
            }

            protected function aclCheckUserPatientAccess(string $squad): bool
            {
                throw new \LogicException('ACL check must not run when the owner is unresolvable');
            }
        };
        $result = $service->delete('11111111-2222-3333-4444-555555555555');

        $this->assertFalse($result->isValid());
        $messages = $this->extractValidationMessages($result);
        $this->assertArrayHasKey('uuid', $messages);
    }

    public function testDeleteRejectsOrphanedListsMedicationUuid(): void
    {
        // findPatientForPrescription() accepts UUIDs from both `prescriptions`
        // and `lists` medication rows (matches the FHIR MedicationRequest
        // getOne surface). delete() only updates the `prescriptions` table,
        // so a lists-only UUID would silently affect zero rows while the
        // API reported success. The prescriptionUuidExists() guard closes
        // that gap.
        $service = new class extends PrescriptionService {
            public function __construct()
            {
                // Skip parent constructor: it hits the DB.
            }

            /**
             * Return type narrowed to `array` (parent declares `?array`);
             * this stub always resolves an owner via the lists surface.
             *
             * @return array<string,mixed>
             */
            public function findPatientForPrescription(string $prescriptionUuid): array
            {
                return ['pid' => 1, 'squad' => '', 'uuid' => 'patient-bytes'];
            }

            protected function prescriptionUuidExists(string $uuid): bool
            {
                return false; // Not in the prescriptions table.
            }
        };
        $result = $service->delete('11111111-2222-3333-4444-555555555555');

        $this->assertFalse(
            $result->isValid(),
            'delete() must reject a UUID that resolves only via the lists medication surface'
        );
        $messages = $this->extractValidationMessages($result);
        $this->assertArrayHasKey('uuid', $messages);
        $this->assertSame(
            [],
            $result->getData(),
            'No "record deleted" message may accompany the rejection'
        );
    }

    public function testInsertPassesSquadTagToAclCheck(): void
    {
        // When the patient carries a squad tag, the ACL seam must be told
        // about it so the `squads/<squad>` gate can run. Locks the plumbing
        // between findPatientByPid and aclCheckUserPatientAccess.
        $capturedSquad = null;
        $service = new class ($capturedSquad) extends PrescriptionService {
            public ?string $captured = null;

            /**
             * @param string|null $captured Passed by reference so the outer
             *                              test scope observes what
             *                              aclCheckUserPatientAccess captured.
             */
            public function __construct(?string &$captured)
            {
                // Skip parent constructor: it hits the DB.
                $this->captured = &$captured;
            }

            /**
             * Return type narrowed to `array` (parent declares `?array`) so
             * PHPStan does not flag the never-null override as an unused
             * type. PHP covariant returns permit this narrowing.
             *
             * @return array<string,mixed>
             */
            protected function findPatientByPid(int $pid): array
            {
                return ['pid' => $pid, 'squad' => 'oncology'];
            }

            protected function aclCheckUserPatientAccess(string $squad): bool
            {
                $this->captured = $squad;
                return false; // Reject so we don't reach the INSERT SQL
            }
        };

        $result = $service->insert([
            'drug' => 'aspirin',
            'patient_id' => 42,
        ]);

        $this->assertSame('oncology', $capturedSquad, 'Squad tag from patient row must be forwarded to the ACL check');
        $this->assertFalse($result->isValid());
    }
}
