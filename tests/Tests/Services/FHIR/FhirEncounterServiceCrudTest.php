<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR Encounter Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirEncounterServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIREncounter $fhirEncounterFixture;
    private FhirEncounterService $fhirEncounterService;
    private string $patientUuid;

    /** @var list<string> uuid strings of encounters created by individual tests, cleaned in tearDown */
    private array $createdEncounterUuids = [];

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        // Install a patient fixture so we have a valid puuid
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        // Load FHIR fixture and set patient reference
        $fixtureData = json_decode(
            file_get_contents(__DIR__ . '/../../Fixtures/FHIR/encounter.json'),
            true
        );
        $fixture = $fixtureData[0];
        $fixture['subject'] = [
            'reference' => 'Patient/' . $this->patientUuid
        ];
        $this->fhirEncounterFixture = new FHIREncounter($fixture);

        $this->fhirEncounterService = new FhirEncounterService();
        $this->fhirEncounterService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        // Delete by exact uuid captured during each test. Falling back to a
        // LIKE 'test-fixture%' sweep silently no-ops if a fixture's text drifts
        // and leaves orphan rows accumulating across runs.
        foreach ($this->createdEncounterUuids as $uuidString) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM form_encounter WHERE uuid = ?",
                [UuidRegistry::uuidToBytes($uuidString)]
            );
        }
        $this->createdEncounterUuids = [];
        // Belt-and-braces sweep for any LIKE-pattern rows still present from
        // previous failed runs (this keeps the test suite self-healing).
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM form_encounter WHERE reason LIKE 'test-fixture%'"
        );
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirEncounterFixture->setId(null);
        $processingResult = $this->fhirEncounterService->insert($this->fhirEncounterFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $processingResult->getData()[0];
        $this->assertArrayHasKey('euuid', $dataResult);
        $this->assertIsString($dataResult['euuid']);
        $this->createdEncounterUuids[] = $dataResult['euuid'];
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        // Remove required fields to trigger validation error
        $this->fhirEncounterFixture->setSubject(null);
        $this->fhirEncounterFixture->setClass(null);
        $processingResult = $this->fhirEncounterService->insert($this->fhirEncounterFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirEncounterService->update('bad-uuid', $this->fhirEncounterFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
