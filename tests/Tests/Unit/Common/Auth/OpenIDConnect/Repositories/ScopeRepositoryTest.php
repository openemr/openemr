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

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
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

        $noopCallback = function (): void { };
        $standardResources = ['facility, patient'];
        $fhirResources = ['Patient', 'Observation'];
        $portalResources = ['patient', 'patient/encounter'];

        $fhirRoutes = $this->makeRoutes('fhir', $fhirResources, $noopCallback);
        // add in some operations and see if we can test this works properly
        $fhirRoutes['Get /fhir/Group/:id/$export'] = $noopCallback;

        $this->scopeRepository->setFhirRouteMap($fhirRoutes);
        $this->scopeRepository->setStandardRouteMap($this->makeRoutes('api', $standardResources, $noopCallback));
        $this->scopeRepository->setPortalRouteMap($this->makeRoutes('portal', $portalResources, $noopCallback));
    }

    private function makeRoutes($routePrefix, $resources, $callback)
    {
        $routes = [];
        foreach ($resources as $resource) {
            $routes['GET /' . $routePrefix . '/' . $resource] = $callback;
            $routes['GET /' . $routePrefix . '/' . $resource . '/:id'] = $callback;
        }
        return $routes;
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

    public function testBuildScopeValidatorArrayCombinedScopesReturnsEverything() {
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

    public function testGetCurrentSmartScopes() {
        $scopeRepository = $this->scopeRepository;

        // let's see if we get the scope
        $smartScopes = $scopeRepository->getCurrentSmartScopes();
        $this->assertNotEmpty($smartScopes, "Smart scopes should not be empty");
        $this->assertContains('system/Patient.read', $smartScopes, "system/Patient.read should be in smart scopes");
        $this->assertContains('system/Observation.read', $smartScopes, "system/Observation.read should be in smart scopes");
        $this->assertContains('patient/Medication.read', $smartScopes, "patient/Medication.read should be in smart scopes");
        $this->assertContains('user/Medication.read', $smartScopes, "user/Medication.read should be in smart scopes");
        $this->assertContains('user/Medication.read', $smartScopes, "user/Medication.read should be in smart scopes");
    }

    public function testGetStandardScopes() {
        $scopeRepository = $this->scopeRepository;

        // let's see if we get the scope
        $smartScopes = $scopeRepository->getStandardApiSupportedScopes();
        $this->assertNotEmpty($smartScopes, "Smart scopes should not be empty");
        $this->assertContains('user/patient.read', $smartScopes, "user/patient.read should be in smart scopes");
        $this->assertContains('user/allergy.read', $smartScopes, "system/allergy.read should be in smart scopes");
        $this->assertContains('system/allergy.read', $smartScopes, "system/allergy.read should be in smart scopes");
        $this->assertContains('system/allergy.write', $smartScopes, "system/allergy.write should be in smart scopes");

    }
}
