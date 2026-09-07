<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGoal;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Services\FHIR\FhirGoalService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Goal Service CRUD Tests
 *
 * Goals are stored in form_care_plan with care_plan_type='goal'. FHIR Goal has no
 * encounter field, so writes require an `encounter-associatedEncounter` FHIR extension
 * pointing at an existing encounter.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirGoalServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRGoal $fhirGoalFixture;
    private FhirGoalService $fhirGoalService;
    private string $patientUuid;
    private string $encounterUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        $this->fixtureManager->installPatientFixtures();
        $patientFixture = $this->fixtureManager->getPatientFixtures()[0];
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        // Create an encounter; Goal writes anchor on it via the encounter extension
        $raw = file_get_contents(__DIR__ . '/../../Fixtures/FHIR/encounter.json');
        $this->assertIsString($raw);
        $encounterRaw = json_decode($raw, true);
        $this->assertIsArray($encounterRaw);
        $encounterPayload = $encounterRaw[0];
        $this->assertIsArray($encounterPayload);
        $encounterPayload['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $encounterResource = new FHIREncounter($encounterPayload);

        $encounterService = new FhirEncounterService();
        $encounterService->setLogger($this->createMock(LoggerInterface::class));
        $encounterInsert = $encounterService->insert($encounterResource);
        $this->assertTrue(
            $encounterInsert->isValid(),
            'Encounter insert (setup) failed: ' . json_encode($encounterInsert->getValidationMessages())
        );
        $encounterUuid = $this->firstDataRow($encounterInsert)['euuid'];
        $this->assertIsString($encounterUuid);
        $this->encounterUuid = $encounterUuid;

        $fixture = (array) $this->fixtureManager->getSingleFhirGoalFixture();
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['extension'] = [[
            'url' => 'http://hl7.org/fhir/StructureDefinition/encounter-associatedEncounter',
            'valueReference' => ['reference' => 'Encounter/' . $this->encounterUuid],
        ]];
        $this->fhirGoalFixture = new FHIRGoal($fixture);

        $this->fhirGoalService = new FhirGoalService();
        $this->fhirGoalService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeGoalFixtures();
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM form_encounter WHERE reason LIKE 'test-fixture%'"
        );
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirGoalFixture->setId(new FHIRId());
        $result = $this->fhirGoalService->insert($this->fhirGoalFixture);
        $this->assertTrue(
            $result->isValid(),
            'Insert should succeed: ' . json_encode($result->getValidationMessages())
        );

        $data = $this->firstDataRow($result);
        $this->assertArrayHasKey('uuid', $data);
        $uuid = $data['uuid'];
        $this->assertIsString($uuid);
        $this->assertStringContainsString('-SK-', $uuid);
    }

    #[Test]
    public function testInsertWithoutEncounterExtensionReturnsValidationError(): void
    {
        $this->fhirGoalFixture->setId(new FHIRId());
        $payload = $this->fhirGoalFixture->jsonSerialize();
        $payload['extension'] = [];
        $fixture = new FHIRGoal($payload);

        $result = $this->fhirGoalService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testInsertWithUnresolvableSubject(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirGoalFixture->setId(new FHIRId());
        $payload = $this->fhirGoalFixture->jsonSerialize();
        $payload['subject'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRGoal($payload);

        $result = $this->fhirGoalService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirGoalFixture->setId(new FHIRId());
        $insertResult = $this->fhirGoalService->insert($this->fhirGoalFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $this->firstDataRow($insertResult)['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirGoalFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $description = $payload['description'] ?? [];
        $this->assertIsArray($description);
        $description['text'] = 'test-fixture updated goal description';
        $payload['description'] = $description;
        $updated = new FHIRGoal($payload);

        $result = $this->fhirGoalService->update($fhirId, $updated);
        $this->assertTrue(
            $result->isValid(),
            'Update should succeed: ' . json_encode($result->getValidationMessages())
        );
        $this->assertNotEmpty($result->getData());
    }

    #[Test]
    public function testUpdateWithBadSurrogateKey(): void
    {
        $result = $this->fhirGoalService->update('bad-uuid', $this->fhirGoalFixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testInsertWithoutLifecycleStatusReturnsValidationError(): void
    {
        $this->fhirGoalFixture->setId(new FHIRId());
        $payload = $this->fhirGoalFixture->jsonSerialize();
        unset($payload['lifecycleStatus']);
        $fixture = new FHIRGoal($payload);

        $result = $this->fhirGoalService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    /**
     * Reads the first row of a ProcessingResult, asserting the shape as it goes so a
     * failed insert surfaces as a test failure rather than a type error downstream.
     *
     * @return array<mixed>
     */
    private function firstDataRow(ProcessingResult $result): array
    {
        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $row = $data[0];
        $this->assertIsArray($row);

        return $row;
    }
}
