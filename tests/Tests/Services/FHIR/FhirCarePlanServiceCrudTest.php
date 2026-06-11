<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCarePlan;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\Services\FHIR\FhirCarePlanService;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR CarePlan Service CRUD Tests
 *
 * Each FHIR CarePlan = one care_plan form on an encounter, aggregating N form_care_plan rows.
 * Tests require an existing encounter, which we build via FhirEncounterService in setUp.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirCarePlanServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRCarePlan $fhirCarePlanFixture;
    private FhirCarePlanService $fhirCarePlanService;
    private string $patientUuid;
    private string $encounterUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patients[0]['pubpid']]
        );
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        // Create an encounter to host the care plan
        $encounterFixtureData = json_decode(
            file_get_contents(__DIR__ . '/../../Fixtures/FHIR/encounter.json'),
            true
        );
        $encounterFixture = $encounterFixtureData[0];
        $encounterFixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $encounterResource = new FHIREncounter($encounterFixture);

        $encounterService = new FhirEncounterService();
        $encounterService->setSystemLogger(new SystemLogger(Level::Critical));
        $encounterResult = $encounterService->insert($encounterResource);
        $this->assertTrue(
            $encounterResult->isValid(),
            "Encounter insert (setup) failed: " . json_encode($encounterResult->getValidationMessages())
        );
        $this->encounterUuid = $encounterResult->getData()[0]['euuid'];

        $fixture = (array) $this->fixtureManager->getSingleFhirCarePlanFixture();
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['encounter'] = ['reference' => 'Encounter/' . $this->encounterUuid];
        $this->fhirCarePlanFixture = new FHIRCarePlan($fixture);

        $this->fhirCarePlanService = new FhirCarePlanService();
        $this->fhirCarePlanService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeCarePlanFixtures();
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM form_encounter WHERE reason LIKE 'test-fixture%'"
        );
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsertCreatesFormAndRows(): void
    {
        $this->fhirCarePlanFixture->setId(null);
        $processingResult = $this->fhirCarePlanService->insert($this->fhirCarePlanFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $data = $processingResult->getData()[0];
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
        $this->assertStringContainsString('-SK-', $data['uuid']);
        $this->assertIsInt($data['form_id']);

        // Two activity entries in the fixture => two form_care_plan rows
        $rowCount = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS c FROM form_care_plan WHERE id = ? AND pid = ?",
            'c',
            [$data['form_id'], $data['pid']]
        );
        $this->assertSame(2, (int) $rowCount);
    }

    #[Test]
    public function testInsertWithoutEncounterReturnsValidationError(): void
    {
        $this->fhirCarePlanFixture->setId(null);
        $this->fhirCarePlanFixture->setEncounter(null);

        $processingResult = $this->fhirCarePlanService->insert($this->fhirCarePlanFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testInsertWithUnresolvableSubject(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirCarePlanFixture->setId(null);
        $payload = $this->fhirCarePlanFixture->jsonSerialize();
        $payload['subject'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRCarePlan($payload);

        $processingResult = $this->fhirCarePlanService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testUpdateReplacesRows(): void
    {
        $this->fhirCarePlanFixture->setId(null);
        $insertResult = $this->fhirCarePlanService->insert($this->fhirCarePlanFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            "Insert should succeed: " . json_encode($insertResult->getValidationMessages())
        );

        $surrogateUuid = $insertResult->getData()[0]['uuid'];
        $formId = $insertResult->getData()[0]['form_id'];

        // Update with a single activity (was 2)
        $payload = $this->fhirCarePlanFixture->jsonSerialize();
        $payload['id'] = $surrogateUuid;
        $payload['activity'] = [[
            'detail' => [
                'description' => 'test-fixture updated single activity',
                'status' => 'completed',
            ],
        ]];
        $updated = new FHIRCarePlan($payload);

        $updateResult = $this->fhirCarePlanService->update($surrogateUuid, $updated);
        $this->assertTrue(
            $updateResult->isValid(),
            "Update should succeed: " . json_encode($updateResult->getValidationMessages())
        );

        $rowCount = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS c FROM form_care_plan WHERE id = ?",
            'c',
            [$formId]
        );
        $this->assertSame(1, (int) $rowCount);

        $description = QueryUtils::fetchSingleValue(
            "SELECT description FROM form_care_plan WHERE id = ?",
            'description',
            [$formId]
        );
        $this->assertSame('test-fixture updated single activity', $description);
    }

    #[Test]
    public function testUpdateWithBadSurrogateKey(): void
    {
        $result = $this->fhirCarePlanService->update('bad-uuid', $this->fhirCarePlanFixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }
}
