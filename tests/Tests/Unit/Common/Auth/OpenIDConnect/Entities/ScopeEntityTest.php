<?php

/*
 * ScopeEntityTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use PHPUnit\Framework\TestCase;

class ScopeEntityTest extends TestCase
{
    public function testContainsScopeWithBackwardsCompatabileReadPermission(): void
    {
        $entityScope = ScopeEntity::createFromString('patient/Patient.read');
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.read')), "read permission should be contained in read permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.r')), "r(read) permission should be contained in read permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.s')), "s(search) permission should be contained in read permission");
    }

    public function testContainsScopeWithBackwardsCompatabileWritePermission(): void
    {
        $entityScope = ScopeEntity::createFromString('patient/Patient.write');
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.write')), "write permission should be contained in write permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.c')), "create permission should be contained in write permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.u')), "update permission should be contained in write permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.d')), "delete permission should be contained in write permission");
    }

    public function testContainsScopeWithFullPermissions(): void
    {
        $entityScope = ScopeEntity::createFromString('patient/Patient.cruds');

        for ($i = 0; $i < 5; $i++) {
            for ($j = $i + 1; $j <= 5; $j++) {
                $checkPermission = substr('cruds', $i, $j);
                $scopeSubstr = 'patient/Patient.' . $checkPermission;
                $scope = ScopeEntity::createFromString($scopeSubstr);
                $this->assertTrue($entityScope->containsScope($scope), "Scope should be contained in the entity scope");
            }
        }
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.read')), "read permission should be contained in cruds permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.write')), "write permission should be contained in cruds permission");
    }

    public function testContainsScopeLimitedReadScope(): void
    {
        $entityScope = ScopeEntity::createFromString('patient/Patient.rs');
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.read')), "read permission should be contained in rs permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.r')), "r(read) permission should be contained in rs permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.s')), "s(search) permission should be contained in rs permission");
    }
    public function testContainsScopeLimitedWriteScope(): void
    {
        $entityScope = ScopeEntity::createFromString('patient/Patient.cud');
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.write')), "write permission should be contained in cud permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.c')), "create permission should be contained in cud permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.u')), "update permission should be contained in cud permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.d')), "delete permission should be contained in cud permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.ud')), "update, delete permission should be contained in cud permission");
        $this->assertTrue($entityScope->containsScope(ScopeEntity::createFromString('patient/Patient.cd')), "create, delete permission should be contained in cud permission");
    }

    public function testGetOperation(): void
    {
        $entity = ScopeEntity::createFromString('patient/*.$export');
        $this->assertEquals('patient/*.$export', $entity->getIdentifier());
        $this->assertEquals("patient", $entity->getContext(), "context should be 'patient'");
        $this->assertEquals("*", $entity->getResource(), "resource should be 'Patient'");
        $this->assertEquals('patient/*', $entity->getScopeLookupKey(), "scopeLookupKey should match the context/resource format");
        $this->assertEquals('$export', $entity->getOperation(), 'operation should be "$export"');
    }

    public function testAddScopePermissions(): void
    {
        $scope1 = ScopeEntity::createFromString('patient/Patient.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern');
        $scope2 = ScopeEntity::createFromString('patient/Patient.r?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|other-health-concern');
        $scope3 = ScopeEntity::createFromString('patient/Patient.rs?status=active');
        $scope1->addScopePermissions($scope2);
        $this->assertEquals($scope1->getPermissions()->getConstraints(), [
            'category' => [
                'http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern',
                'http://hl7.org/fhir/us/core/CodeSystem/condition-category|other-health-concern'
            ]
        ], "permissions constraints should match the combined query parameters");

        $scope1->addScopePermissions($scope3);
        $this->assertEquals($scope1->getPermissions()->getConstraints(), [
            'category' => [
                'http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern',
                'http://hl7.org/fhir/us/core/CodeSystem/condition-category|other-health-concern'
            ]
            ,'status' => 'active'
        ], "permissions constraints should match the combined query parameters");
    }

    public function testCreateFromStringSMARTLaunchScope(): void
    {
        $entity = ScopeEntity::createFromString('launch/patient');
        $this->assertEquals('launch/patient', $entity->getIdentifier());
        $this->assertEmpty($entity->getOperation());
        $this->assertEquals("launch", $entity->getContext());
        $this->assertEquals("patient", $entity->getResource());
    }

    public function testCreateFromStringSingleWordScope(): void
    {
        $entity = ScopeEntity::createFromString('fhirUser');
        $this->assertEquals('fhirUser', $entity->getIdentifier());
        $this->assertEquals($entity->getIdentifier(), $entity->getContext());
        $this->assertEmpty($entity->getResource());
        $this->assertEmpty($entity->getOperation());
    }

    public function testCreateFromStringScopeWithFullCRUDSPermission(): void
    {
        $entity = ScopeEntity::createFromString('patient/Patient.cruds');
        $this->assertEquals('patient/Patient.cruds', $entity->getIdentifier());
        $this->assertEquals("patient", $entity->getContext(), "context should be 'patient'");
        $this->assertEquals("Patient", $entity->getResource(), "resource should be 'Patient'");
        $this->assertEquals('patient/Patient', $entity->getScopeLookupKey(), "scopeLookupKey should match the context/resource format");
    }

    public function testCreateFromStringScopeWithUnderscore(): void
    {
        $entity = ScopeEntity::createFromString('patient/medical_problem.cruds');
        $this->assertEquals('patient/medical_problem.cruds', $entity->getIdentifier());
        $this->assertEquals("patient", $entity->getContext(), "context should be 'patient'");
        $this->assertEquals("medical_problem", $entity->getResource(), "resource should be 'medical_problem'");
        $this->assertEquals('patient/medical_problem', $entity->getScopeLookupKey(), "scopeLookupKey should match the context/resource format");

        $entity = ScopeEntity::createFromString('given_name');
        $this->assertEquals('given_name', $entity->getIdentifier());
        $this->assertEquals("given_name", $entity->getContext(), "context should be 'given_name'");
        $this->assertEmpty($entity->getResource(), "resource should be empty");
        $this->assertEquals('given_name', $entity->getScopeLookupKey(), "scopeLookupKey should match the context/resource format");
    }

    public function testCreateFromStringScopeWithColon(): void
    {
        $entity = ScopeEntity::createFromString('api:oemr');
        $this->assertEquals('api:oemr', $entity->getIdentifier());
        $this->assertEquals("api", $entity->getContext(), "context should be 'api'");
        $this->assertEquals("oemr", $entity->getResource(), "resource should be 'oemr'");
        $this->assertEquals('api/oemr', $entity->getScopeLookupKey(), "scopeLookupKey should match the context/resource format");
    }

    public function testCreateFromStringScopeWithNumbers(): void
    {
        $entity = ScopeEntity::createFromString('patient/patient2.cruds');
        $this->assertEquals('patient/patient2.cruds', $entity->getIdentifier());
        $this->assertEquals("patient", $entity->getContext(), "context should be 'patient'");
        $this->assertEquals("patient2", $entity->getResource(), "resource should be 'patient2'");
        $this->assertEquals('patient/patient2', $entity->getScopeLookupKey(), "scopeLookupKey should match the context/resource format");
    }

    public function testCreateFromStringScopeWithDashes(): void
    {
        $entity = ScopeEntity::createFromString('patient/medical-problem.cruds');
        $this->assertEquals('patient/medical-problem.cruds', $entity->getIdentifier());
        $this->assertEquals("patient", $entity->getContext(), "context should be 'patient'");
        $this->assertEquals("medical-problem", $entity->getResource(), "resource should be 'medical-problem'");
        $this->assertEquals('patient/medical-problem', $entity->getScopeLookupKey(), "scopeLookupKey should match the context/resource format");
    }

    public function testCreateFromStringScopeWithCondition(): void
    {
        $scope = 'patient/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern';
        $entity = ScopeEntity::createFromString($scope);
        $this->assertEquals($scope, $entity->getIdentifier());
        $this->assertEquals("patient", $entity->getContext(), "context should be 'patient'");
        $this->assertEquals("Condition", $entity->getResource(), "resource should be 'Condition'");
        $this->assertEquals('patient/Condition', $entity->getScopeLookupKey(), "scopeLookupKey should match the context/resource format");
        $permissions = $entity->getPermissions();
        $this->assertEquals('rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern', $permissions->getIdentifier(), "permissions identifier should be set");
        $this->assertEquals($permissions->getConstraints(), [
            'category' => 'http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern'
        ], "permissions constraints should match the query parameters");
    }

    public function testCreateFromStringWithInvalidScopeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ScopeEntity::createFromString('patient/Patient/invalidscope');
    }

    public function testCreateFromStringWithInvalidScopeResourceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ScopeEntity::createFromString('patient.Patient/invalidscope');
    }
}
