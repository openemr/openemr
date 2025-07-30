<?php

/**
 * ScopeRepositoryTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\Repositories;

use Google\Service\AppHub\Scope;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\Tests\MockRestConfig;
use PHPUnit\Framework\TestCase;

class ScopeRepositoryTest extends TestCase
{
    /**
     * @var ScopeRepository
     */
    private $scopeRepository;

    public function setUp(): void
    {
        $mock = new MockRestConfig();
        $mock::$systemScopesEnabled = true;
        $mock = $this->createMock(ServerConfig::class);
        $mock->method('areSystemScopesEnabled')
            ->willReturn(true);
        $this->scopeRepository = new ScopeRepository();
        $this->scopeRepository->setServerConfig($mock);
    }

    public function testHasFhirApiScopes(): void
    {
        $this->scopeRepository->setRequestScopes('api:oemr');
        $this->assertFalse($this->scopeRepository->hasFhirApiScopes(), "Standard api request turn off fhir api");

        $this->scopeRepository->setRequestScopes('api:fhir');
        $this->assertTrue($this->scopeRepository->hasFhirApiScopes(), "api:fhir scope should trigger fhir api");
    }

    public function testHasStandardApiScopes(): void
    {
        $this->scopeRepository->setRequestScopes('api:oemr');
        $this->assertTrue($this->scopeRepository->hasStandardApiScopes(), "Standard api request turned on");

        $this->scopeRepository->setRequestScopes('api:blah');
        $this->assertFalse($this->scopeRepository->hasStandardApiScopes(), "Standard api request turned off mispelled 'api:oemr2'");

        $this->scopeRepository->setRequestScopes('api:fhir');
        $this->assertFalse($this->scopeRepository->hasStandardApiScopes(), "api:fhir scope should turn off standard api");
    }

    public function testBuildScopeValidatorArrayForStandardApiScopeRequest(): void
    {
        // check to make sure we get standard api scopes for the correct test screen
        $scopeRepository = $this->scopeRepository;
        $expectedScopes = $scopeRepository->getCurrentStandardScopes();

        $scopeRepository->setRequestScopes("api:oemr");
        $validatorArray = array_keys($scopeRepository->buildScopeValidatorArray());

        $diff = array_diff($expectedScopes, $validatorArray);
        $this->assertEquals([], $diff, "OpenEMR api scope of 'api:oemr' should return standard scopes");
        $this->assertContains('user/patient.read', $validatorArray, "user/patient.read should be in standard api scopes");
        $this->assertContains('user/allergy.read', $validatorArray, "user/allergy.read should be in standard api scopes");
    }

    public function testBuildScopeValidatorArrayForStandardPortalApiScopeRequest(): void
    {
        // check to make sure we get standard api scopes for the correct test screen
        $scopeRepository = $this->scopeRepository;
        $expectedScopes = $scopeRepository->getCurrentStandardScopes();

        $scopeRepository->setRequestScopes("api:port");
        $validatorArray = array_keys($scopeRepository->buildScopeValidatorArray());

        $diff = array_diff($expectedScopes, $validatorArray);
        $this->assertEquals([], $diff, "OpenEMR api scope of 'api:port' should return standard scopes");
    }

    public function testBuildScopeValidatorArrayDefaultReturnsFhirScopes(): void
    {
        // check to make sure we get standard api scopes for the correct test screen
        $scopeRepository = $this->scopeRepository;
        $expectedScopes = $scopeRepository->getCurrentSmartScopes();

        $scopeRepository->setRequestScopes("");
        $validatorArray = array_keys($scopeRepository->buildScopeValidatorArray());

        $diff = array_diff($expectedScopes, $validatorArray);
        $this->assertEquals([], $diff, "OpenEMR api scope of 'api:port' should return standard scopes");
    }

    public function testBuildScopeValidatorArrayCombinedScopesReturnsEverything(): void
    {
        $scopeRepository = $this->scopeRepository;
        $expectedScopes = $scopeRepository->getCurrentSmartScopes();
        $expectedScopes = array_merge($expectedScopes, $scopeRepository->getCurrentStandardScopes());

        $scopeRepository->setRequestScopes("api:oemr api:port api:fhir");
        $validatorArray = array_keys($scopeRepository->buildScopeValidatorArray());
        $diff = array_diff($expectedScopes, $validatorArray);
        $this->assertEquals([], $diff, "OpenEMR api scope of 'api:oemr api:port api:fhir' should return all scopes");
    }


    public function testGetScopeEntityByIdentifierHasExportOperations(): void
    {
        $scopeRepository = $this->scopeRepository;
        $scopeRepository->setRequestScopes('system/Group.$export system/Patient.$export system/*.$export');

        // let's see if we get the scope
        $scopeEntity = $scopeRepository->getScopeEntityByIdentifier('system/Group.$export');
        $this->assertNotEmpty($scopeEntity, "system/Group.\$export not found in FHIR route map");

        $scopeEntity = $scopeRepository->getScopeEntityByIdentifier('system/Patient.$export');
        $this->assertNotEmpty($scopeEntity, "system/Patient.\$export not found in FHIR route map");

        $scopeEntity = $scopeRepository->getScopeEntityByIdentifier('system/*.$export');
        $this->assertNotEmpty($scopeEntity, "system/*.\$export not found in FHIR route map");
    }

    public function testGetCurrentSmartScopes(): void
    {
        $scopeRepository = $this->scopeRepository;

        // let's see if we get the scope
        $smartScopes = $scopeRepository->getCurrentSmartScopes();
        $this->assertNotEmpty($smartScopes, "Smart scopes should not be empty");
        $resourceChecks = ['Patient', 'Observation', 'MedicationRequest'];
        foreach ($resourceChecks as $resource) {
            $this->assertContains("system/{$resource}.read", $smartScopes, "system/{$resource}.read should be in smart scopes");
            $this->assertContains("patient/{$resource}.read", $smartScopes, "patient/{$resource}.read should be in smart scopes");
            $this->assertContains("user/{$resource}.read", $smartScopes, "user/{$resource}.read should be in smart scopes");
        }

        // now do v2 checks
        foreach ($resourceChecks as $resource) {
            $this->assertContains("system/{$resource}.rs", $smartScopes, "system/{$resource}.rs should be in smart scopes");
            $this->assertContains("patient/{$resource}.rs", $smartScopes, "patient/{$resource}.rs should be in smart scopes");
            $this->assertContains("user/{$resource}.rs", $smartScopes, "user/{$resource}.rs should be in smart scopes");
        }
    }



    public function testGetStandardScopes(): void
    {
        $scopeRepository = $this->scopeRepository;

        // let's see if we get the scope
        $smartScopes = $scopeRepository->getStandardApiSupportedScopes();
        $this->assertNotEmpty($smartScopes, "Smart scopes should not be empty");
        $this->assertContains('user/patient.read', $smartScopes, "user/patient.read should be in smart scopes");
        $this->assertContains('user/allergy.read', $smartScopes, "system/allergy.read should be in smart scopes");
        $this->assertContains('system/allergy.read', $smartScopes, "system/allergy.read should be in smart scopes");
        $this->assertContains('system/allergy.write', $smartScopes, "system/allergy.write should be in smart scopes");
    }

    public function testGetScopeByIdentifierWithReadV2Scopes() : void {
        $scopeRepository = new ScopeRepository();
        $scopeRepository->setRequestScopes("patient/Patient.rs");
        $scope = $scopeRepository->getScopeEntityByIdentifier("patient/Patient.r");
        $this->assertNotEmpty($scope, "patient/Patient.r scope should be valid scope with patient/Patient.rs request scope");
        $scope = $scopeRepository->getScopeEntityByIdentifier("patient/Patient.rs");
        $this->assertNotEmpty($scope, "patient/Patient.rs scope should be valid scope with patient/Patient.rs request scope");
        $scope = $scopeRepository->getScopeEntityByIdentifier("patient/Patient.read");
        $this->assertNotEmpty($scope, "patient/Patient.read scope should be valid scope with patient/Patient.rs request scope for backwards compatability");
    }

    public function testGetScopeByIdentifierWithWriteV2Scopes() : void {
        $scopeRepository = new ScopeRepository();
        $scopeRepository->setRequestScopes("user/medical_problem.cud");
        $scope = $scopeRepository->getScopeEntityByIdentifier("user/medical_problem.c");
        $this->assertNotEmpty($scope, "patient/Patient.c scope should be valid scope with user/medical_problem.cud request scope");
        $scope = $scopeRepository->getScopeEntityByIdentifier("user/medical_problem.u");
        $this->assertNotEmpty($scope, "patient/Patient.u scope should be valid scope with user/medical_problem.cud request scope");
        $scope = $scopeRepository->getScopeEntityByIdentifier("user/medical_problem.d");
        $this->assertNotEmpty($scope, "patient/Patient.read scope should be valid scope with user/medical_problem.cud request scope for backwards compatability");

        $scope = $scopeRepository->getScopeEntityByIdentifier("user/medical_problem.write");
        $this->assertNotEmpty($scope, "user/medical_problem.write scope should be valid scope with patient/Patient.rs request scope for backwards compatability");
    }
}
