<?php

/**
 * ScopeEntityTest.php
 *
 * Tests for ScopeEntity::containsScope(), in particular the v1 (read/write)
 * permission handling. Prior to this fix, containsScope() returned true for
 * any scope requesting v1Read or v1Write without checking whether the
 * containing scope actually granted those permissions.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use PHPUnit\Framework\TestCase;

class ScopeEntityTest extends TestCase
{
    public function testContainsScopeIdenticalScopes(): void
    {
        $scope = ScopeEntity::createFromString("patient/Patient.read");
        $other = ScopeEntity::createFromString("patient/Patient.read");
        $this->assertTrue($scope->containsScope($other), "A scope should contain an identical scope");
    }

    public function testContainsScopeDifferentResource(): void
    {
        $scope = ScopeEntity::createFromString("patient/Patient.read");
        $other = ScopeEntity::createFromString("patient/Observation.read");
        $this->assertFalse($scope->containsScope($other), "Scopes with different resources should not contain each other");
    }

    /**
     * Regression test: before the fix, a scope with only update permission was
     * reported as containing a v1 read scope because containsScope() returned
     * true whenever the other scope had v1Read set, without checking $this.
     */
    public function testContainsScopeDeniesV1ReadWhenNotGranted(): void
    {
        $scope = ScopeEntity::createFromString("patient/Patient.u");
        $other = ScopeEntity::createFromString("patient/Patient.read");
        $this->assertFalse($scope->containsScope($other), "An update-only scope must not contain a v1 read scope");
    }

    /**
     * Regression test: same bypass as above but for v1Write.
     */
    public function testContainsScopeDeniesV1WriteWhenNotGranted(): void
    {
        $scope = ScopeEntity::createFromString("patient/Patient.r");
        $other = ScopeEntity::createFromString("patient/Patient.write");
        $this->assertFalse($scope->containsScope($other), "A read-only scope must not contain a v1 write scope");
    }

    /**
     * v1 'read' is equivalent to v2 'rs' (read + search), so containment
     * should hold in both directions.
     */
    public function testContainsScopeV1ReadEquivalentToReadSearch(): void
    {
        $v2 = ScopeEntity::createFromString("patient/Patient.rs");
        $v1 = ScopeEntity::createFromString("patient/Patient.read");
        $this->assertTrue($v2->containsScope($v1), "'rs' should contain v1 'read'");
        $this->assertTrue($v1->containsScope($v2), "v1 'read' should contain 'rs'");
    }

    /**
     * v1 'write' is equivalent to v2 'cud' (create + update + delete), so
     * containment should hold in both directions.
     */
    public function testContainsScopeV1WriteEquivalentToCud(): void
    {
        $v2 = ScopeEntity::createFromString("patient/Patient.cud");
        $v1 = ScopeEntity::createFromString("patient/Patient.write");
        $this->assertTrue($v2->containsScope($v1), "'cud' should contain v1 'write'");
        $this->assertTrue($v1->containsScope($v2), "v1 'write' should contain 'cud'");
    }

    public function testContainsScopeWriteDoesNotContainRead(): void
    {
        $write = ScopeEntity::createFromString("patient/Patient.write");
        $read = ScopeEntity::createFromString("patient/Patient.read");
        $this->assertFalse($write->containsScope($read), "v1 'write' must not contain v1 'read'");
        $this->assertFalse($read->containsScope($write), "v1 'read' must not contain v1 'write'");
    }

    /**
     * The v1 flags are normally derived from the CRUD flags by
     * ScopePermissionObject::createFromString(). Exercise the new checks
     * directly by setting v1Read on the requested scope's permission object,
     * so containment cannot pass by way of the CRUD checks alone.
     */
    public function testContainsScopeChecksV1FlagsIndependentlyOfCrudFlags(): void
    {
        // note: identifiers must differ or the identity short-circuit in
        // containsScope() returns true before any permission checks run
        $scope = ScopeEntity::createFromString("patient/Patient.cr");
        $other = ScopeEntity::createFromString("patient/Patient.c");
        $other->getPermissions()->v1Read = true;
        $this->assertFalse($scope->containsScope($other), "A scope without v1Read must not contain a scope requiring v1Read");
    }

    /**
     * Regression test for GHSA-6cmr-r2rh-755m. A confidential client approved
     * only for a read-only user-context scope must not be treated as containing
     * the corresponding write scope. This is the exact shape reported: a
     * user/<resource>.read registration was preserving user/<resource>.write
     * at scope finalization. Covers the user context and the appointment
     * resource from the reported proof of concept, alongside the patient-context
     * cases above.
     */
    public function testContainsScopeReadOnlyClientDoesNotContainWriteUserContext(): void
    {
        $appointmentRead = ScopeEntity::createFromString("user/appointment.read");
        $appointmentWrite = ScopeEntity::createFromString("user/appointment.write");
        $this->assertFalse(
            $appointmentRead->containsScope($appointmentWrite),
            "user/appointment.read must not contain user/appointment.write"
        );

        $patientRead = ScopeEntity::createFromString("user/patient.read");
        $patientWrite = ScopeEntity::createFromString("user/patient.write");
        $this->assertFalse(
            $patientRead->containsScope($patientWrite),
            "user/patient.read must not contain user/patient.write"
        );
    }
}
