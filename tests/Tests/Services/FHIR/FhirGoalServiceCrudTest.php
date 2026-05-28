<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGoal;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Services\FHIR\FhirGoalService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR Goal Service CRUD Tests
 *
 * Goals are stored in form_care_plan with care_plan_type='goal'. FHIR Goal has no
 * encounter field, so writes require an `encounter-associatedEncounter` FHIR extension
 * pointing at an existing encounter.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
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
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        // Create an encounter; Goal writes anchor on it via the encounter extension
        $encounterRaw = json_decode(
            file_get_contents(__DIR__ . '/../../Fixtures/FHIR/encounter.json'),
            true
        );
        $encounterPayload = $encounterRaw[0];
        $encounterPayload['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $encounterResource = new FHIREncounter($encounterPayload);

        $encounterService = new FhirEncounterService();
        $encounterService->setSystemLogger(new SystemLogger(Level::Critical));
        $encounterInsert = $encounterService->insert($encounterResource);
        $this->assertTrue(
            $encounterInsert->isValid(),
            'Encounter insert (setup) failed: ' . json_encode($encounterInsert->getValidationMessages())
        );
        $this->encounterUuid = $encounterInsert->getData()[0]['euuid'];

        $fixture = (array) $this->fixtureManager->getSingleFhirGoalFixture();
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['extension'] = [[
            'url' => 'http://hl7.org/fhir/StructureDefinition/encounter-associatedEncounter',
            'valueReference' => ['reference' => 'Encounter/' . $this->encounterUuid],
        ]];
        $this->fhirGoalFixture = new FHIRGoal($fixture);

        $this->fhirGoalService = new FhirGoalService();
        $this->fhirGoalService->setSystemLogger(new SystemLogger(Level::Critical));
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
        $this->fhirGoalFixture->setId(null);
        $result = $this->fhirGoalService->insert($this->fhirGoalFixture);
        $this->assertTrue(
            $result->isValid(),
            'Insert should succeed: ' . json_encode($result->getValidationMessages())
        );

        $data = $result->getData()[0];
        $this->assertArrayHasKey('uuid', $data);
        $this->assertStringContainsString('-SK-', $data['uuid']);
    }

    #[Test]
    public function testInsertWithoutEncounterExtensionReturnsValidationError(): void
    {
        $this->fhirGoalFixture->setId(null);
        $payload = $this->fhirGoalFixture->jsonSerialize();
        $payload['extension'] = [];
        $fixture = new FHIRGoal($payload);

        $result = $this->fhirGoalService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }

    #[Test]
    public function testInsertWithUnresolvableSubject(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirGoalFixture->setId(null);
        $payload = $this->fhirGoalFixture->jsonSerialize();
        $payload['subject'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRGoal($payload);

        $result = $this->fhirGoalService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirGoalFixture->setId(null);
        $insertResult = $this->fhirGoalService->insert($this->fhirGoalFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $insertResult->getData()[0]['uuid'];

        $payload = $this->fhirGoalFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['description']['text'] = 'test-fixture updated goal description';
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
        $this->assertEquals(0, count($result->getData()));
    }

    #[Test]
    public function testInsertWithoutLifecycleStatusReturnsValidationError(): void
    {
        $this->fhirGoalFixture->setId(null);
        $payload = $this->fhirGoalFixture->jsonSerialize();
        unset($payload['lifecycleStatus']);
        $fixture = new FHIRGoal($payload);

        $result = $this->fhirGoalService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertEquals(0, count($result->getData()));
    }
}
