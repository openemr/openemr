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
use Monolog\Level;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ResourceScopeEntityList;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
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

    public function testBuildScopeValidatorArrayCombinedScopesReturnsEverything(): void
    {
        $scopeRepository = $this->scopeRepository;
        $expectedScopes = $scopeRepository->getCurrentSmartScopes();
        $validatorArray = $scopeRepository->buildScopeValidatorArray($expectedScopes);
//        $this->assertNotEmpty($validatorArray, "Scope validator array should not be empty");
        $scopes = [];
        foreach ($validatorArray as $scopeResourceList) {
            foreach ($scopeResourceList as $scopeEntity) {
                $scopes[] = $scopeEntity->getIdentifier();
            }
        }
        $diff = array_diff($expectedScopes, $scopes);
        $this->assertEquals([], $diff, "OpenEMR api scope of 'api:oemr api:port api:fhir' should return all scopes");
    }

    public function testBuildScopeValidatorArrayMultipleOperations(): void
    {
        $serverScopeListEntity = $this->getMockBuilder(ServerScopeListEntity::class)
            ->onlyMethods(['getAllSupportedScopesList'])
            ->getMock();
        $scopeRepository = $this->scopeRepository;
        $scopeRepository->setServerScopeList($serverScopeListEntity);
        $validatorArray = $scopeRepository->buildScopeValidatorArray([
            "user/Patient.read"
            , 'user/Patient.$export'
            // these other operations are just for testing right now, not supported in repo
            , 'user/Patient.$summary'
            , 'user/Patient.$version'
        ]);
        $this->assertArrayHasKey("user/Patient", $validatorArray, "user/Patient should be in scope validator array");
        $this->assertCount(4, $validatorArray["user/Patient"], "user/Patient should have 4 ScopeEntity objects in scope validator array");
        $operations = $validatorArray["user/Patient"];
        $this->assertInstanceOf(ResourceScopeEntityList::class, $operations, "user/Patient should be a ResourceScopeEntityList object in scope validator array");
        $this->assertEquals('user/Patient.read', $operations[0]->getIdentifier(), "user/Patient.read should be in scope validator array");
        $this->assertEquals('user/Patient.$export', $operations[1]->getIdentifier(), "user/Patient.\$export should be in scope validator array");
        $this->assertEquals('user/Patient.$summary', $operations[2]->getIdentifier(), "user/Patient.\$summary should be in scope validator array");
        $this->assertEquals('user/Patient.$version', $operations[3]->getIdentifier(), "user/Patient.\$version should be in scope validator array");
    }

    public function testGetScopeEntityByIdentifierWithSubResourceScope(): void
    {
        $serverScopeListEntity = $this->getMockBuilder(ServerScopeListEntity::class)
            ->onlyMethods(['getAllSupportedScopesList'])
            ->getMock();
        $serverScopeListEntity->expects($this->any())
            ->method('getAllSupportedScopesList')
            ->willReturn([
                "user/Patient.cruds"
                ,'user/medical_problem.cruds'
            ]);
        $scopeRepository = $this->scopeRepository;
        // skip over expected error logs
        $scopeRepository->setSystemLogger(new SystemLogger(Level::Critical));
        $scopeRepository->setServerScopeList($serverScopeListEntity);

        $validSubSets = [
            'c'
            , 'cr', 'cru', 'crud', 'cruds', 'cu', 'cud', 'cuds', 'cd', 'cds', 'cs'
            , 'r', 'ru', 'rud', 'ruds', 'rd', 'rds', 'rs'
            , 'u', 'ud', 'uds', 'd', 'ds'
            , 'd', 'ds'
            , 's'
        ];
        $invalidSubSets = [
            'rc', 'rcu', 'rcud', 'rcuds', 'rdc', 'rdcu','rdcus', 'rsc', 'rscu', 'rscud', 'ruc', 'rucd', 'rucds'
            ,'cur', 'curd', 'curds', 'cudr', 'cudrs', 'cudrsc'
            ,'cc', 'rr', 'ss', 'dd', 'uu', 'csu'
            ,'uc', 'ur', 'usc', 'udc'
            ,'sc', 'sr', 'su', 'sd'
        ];
        foreach ($validSubSets as $subset) {
            $scopeIdentifier = $scopeRepository->getScopeEntityByIdentifier("user/Patient.{$subset}");
            $this->assertNotEmpty($scopeIdentifier, "user/Patient.{$subset} scope should be valid scope");

            $scopeIdentifier = $scopeRepository->getScopeEntityByIdentifier("user/medical_problem.{$subset}");
            $this->assertNotEmpty($scopeIdentifier, "user/medical_problem.{$subset} scope should be valid scope");
        }
        foreach ($invalidSubSets as $subset) {
            $scopeIdentifier = $scopeRepository->getScopeEntityByIdentifier("user/Patient.{$subset}");
            $this->assertEmpty($scopeIdentifier, "user/Patient.{$subset} scope should not be valid scope");

            $scopeIdentifier = $scopeRepository->getScopeEntityByIdentifier("user/medical_problem.{$subset}");
            $this->assertEmpty($scopeIdentifier, "user/medical_problem.{$subset} scope should not be valid scope");
        }
    }

    public function testGetScopeEntityByIdentifierHasExportOperations(): void
    {
        $scopeRepository = $this->scopeRepository;
        $serverScopeListEntity = $this->getMockBuilder(ServerScopeListEntity::class)
            ->onlyMethods(['getAllSupportedScopesList'])
            ->getMock();
        $serverScopeListEntity->expects($this->once())
            ->method('getAllSupportedScopesList')
            ->willReturn([
                'system/Group.$export',
                'system/Patient.$export',
                'system/*.$export'
            ]);
        $scopeRepository->setServerScopeList($serverScopeListEntity);

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
        $serverScopeListEntity = $this->getMockBuilder(ServerScopeListEntity::class)
            ->onlyMethods(['getAllSupportedScopesList'])
            ->getMock();
        $serverScopeListEntity->expects($this->any())
            ->method('getAllSupportedScopesList')
            ->willReturn([
                "patient/Patient.read",
                "patient/Observation.read",
                "patient/MedicationRequest.read",
                "user/Patient.read",
                "user/Observation.read",
                "user/MedicationRequest.read",
                "system/Patient.read",
                "system/Observation.read",
                "system/MedicationRequest.read",
                "patient/Patient.rs",
                "patient/Observation.rs",
                "patient/MedicationRequest.rs",
                "user/Patient.rs",
                "user/Observation.rs",
                "user/MedicationRequest.rs",
                "system/Patient.rs",
                "system/Observation.rs",
                "system/MedicationRequest.rs",
            ]);

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

    public function testFinalizeScopesWithValidSubScopeWillPass(): void
    {
        // verify a client with a full scope, will still pass if a request is made for a sub scope
        $scopeRepository = $this->scopeRepository;
        $requestedScopes = [
            ScopeEntity::createFromString("patient/Patient.rs")
            ,ScopeEntity::createFromString("patient/Patient.cud")
            ,ScopeEntity::createFromString("patient/Patient.ruds")
            ,ScopeEntity::createFromString("launch/patient")
            ,ScopeEntity::createFromString("launch")
            ,ScopeEntity::createFromString("api:fhir")
            ,ScopeEntity::createFromString("api:portal")
            ,ScopeEntity::createFromString("api:oemr")
            ,ScopeEntity::createFromString("openid")
        ];
        $client = new ClientEntity();
        $client->setScopes([
            "patient/Patient.cruds"
        ]);
        $allowedScopes = $scopeRepository->finalizeScopes($requestedScopes, "authorization_code", $client);
        $this->assertCount(3, $allowedScopes, "Should have 3 scopes in allowed scopes");
        $this->assertEquals("patient/Patient.rs", $allowedScopes[0]->getIdentifier(), "Allowed scope should be patient/Patient.rs");
        $this->assertEquals("patient/Patient.cud", $allowedScopes[1]->getIdentifier(), "Allowed scope should be patient/Patient.cud");
        $this->assertEquals("patient/Patient.ruds", $allowedScopes[2]->getIdentifier(), "Allowed scope should be patient/Patient.ruds");
    }

    public function finalizeScopesWithInvalidSubScopeWillFail(): never
    {
        // tests we need to handle here
        $this->markTestIncomplete("ScopeRepository::finalizeScopes() needs to be implemented and tested.");

        // verify a client with a full scope, will still fail if a request is made for an invalid sub scope
        $scopeRepository = $this->scopeRepository;
        $requestedScopes = [
            ScopeEntity::createFromString("patient/Patient.cud")
        ];
        $client = new ClientEntity();
        $client->setScopes([
            "patient/Patient.rs"
            ,"patient/Patient.r"
            ,"patient/Patient.s"
            ,"openid"
            ,"api:fhir"
            ,"api:oemr"
            ,"api:portal"
        ]);
        $allowedScopes = $scopeRepository->finalizeScopes($requestedScopes, "authorization_code", $client);
        $this->assertEmpty($allowedScopes, "Allowed scope should be empty for patient/Patient.rs as its not a subset of Patient/Patient.cud");
    }
}
