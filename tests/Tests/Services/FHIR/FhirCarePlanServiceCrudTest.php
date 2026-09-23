<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCarePlan;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\FhirCarePlanService;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR CarePlan Service CRUD Tests
 *
 * Each FHIR CarePlan = one care_plan form on an encounter, aggregating N form_care_plan rows.
 * Tests require an existing encounter, which we build via FhirEncounterService in setUp.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
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
        $patientFixture = $patients[0];
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        // Create an encounter to host the care plan
        $raw = file_get_contents(__DIR__ . '/../../Fixtures/FHIR/encounter.json');
        $this->assertIsString($raw);
        $encounterFixtureData = json_decode($raw, true);
        $this->assertIsArray($encounterFixtureData);
        $encounterFixture = $encounterFixtureData[0];
        $this->assertIsArray($encounterFixture);
        $encounterFixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $encounterResource = new FHIREncounter($encounterFixture);

        $encounterService = new FhirEncounterService();
        $encounterService->setLogger($this->createMock(LoggerInterface::class));
        $encounterResult = $encounterService->insert($encounterResource);
        $this->assertTrue(
            $encounterResult->isValid(),
            "Encounter insert (setup) failed: " . json_encode($encounterResult->getValidationMessages())
        );
        $encounterUuid = $this->firstDataRow($encounterResult)['euuid'];
        $this->assertIsString($encounterUuid);
        $this->encounterUuid = $encounterUuid;

        $fixture = (array) $this->fixtureManager->getSingleFhirCarePlanFixture();
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['encounter'] = ['reference' => 'Encounter/' . $this->encounterUuid];
        $this->fhirCarePlanFixture = new FHIRCarePlan($fixture);

        $this->fhirCarePlanService = new FhirCarePlanService();
        $this->fhirCarePlanService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeCarePlanFixtures();
        // This run's encounter only. The old sweep matched reason LIKE 'test-fixture%', which
        // also deletes the encounters of any other worker running this suite concurrently and
        // fails them with rows that disappeared mid-test. setUp() already captured the uuid.
        if (isset($this->encounterUuid)) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM form_encounter WHERE uuid = ?",
                [UuidRegistry::uuidToBytes($this->encounterUuid)]
            );
        }
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsertCreatesFormAndRows(): void
    {
        $this->fhirCarePlanFixture->setId(new FHIRId());
        $processingResult = $this->fhirCarePlanService->insert($this->fhirCarePlanFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $data = $this->firstDataRow($processingResult);
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
        $this->assertIsNumeric($rowCount);
        $this->assertSame(2, (int) $rowCount);
    }

    #[Test]
    public function testInsertWithoutEncounterReturnsValidationError(): void
    {
        $this->fhirCarePlanFixture->setId(new FHIRId());
        $this->fhirCarePlanFixture->setEncounter(new FHIRReference());

        $processingResult = $this->fhirCarePlanService->insert($this->fhirCarePlanFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testInsertWithUnknownActivityCodeSystemIsRejected(): void
    {
        // As in Medication: an activity detail code whose system OpenEMR cannot map has no
        // TYPE: prefix to store it under, so it would read back under a system the caller
        // never sent. The whole write is refused rather than the activity silently dropped --
        // dropping one would let a create store fewer activities than were sent and still
        // answer 201.
        $this->fhirCarePlanFixture->setId(new FHIRId());
        $payload = $this->fhirCarePlanFixture->jsonSerialize();
        $activities = $payload['activity'] ?? [];
        $this->assertIsArray($activities);
        $this->assertArrayHasKey(0, $activities);
        $this->assertIsArray($activities[0]);
        $detail = $activities[0]['detail'] ?? [];
        $this->assertIsArray($detail);
        $code = $detail['code'] ?? [];
        $this->assertIsArray($code);
        $coding = $code['coding'] ?? [];
        $this->assertIsArray($coding);
        $this->assertArrayHasKey(0, $coding);
        $this->assertIsArray($coding[0]);
        $coding[0]['system'] = 'http://example.org/not-a-code-system-openemr-knows';
        $code['coding'] = $coding;
        $detail['code'] = $code;
        $activities[0]['detail'] = $detail;
        $payload['activity'] = $activities;
        $fixture = new FHIRCarePlan($payload);

        $processingResult = $this->fhirCarePlanService->insert($fixture);
        $this->assertFalse(
            $processingResult->isValid(),
            'An activity code with an unmappable system should be refused, not stored bare'
        );
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testInsertWithUnresolvableSubject(): void
    {
        // getUnregisteredUuid(), not UuidRegistry::createUuid(): createUuid() inserts a
        // uuid_registry row, and this test never creates the patient_data row that would carry
        // it, so teardown -- which finds rows through their targets -- cannot see it and
        // the registry row survives every run. An unregistered uuid is just as
        // unresolvable, which is all the test needs.
        $bogusUuid = $this->fixtureManager->getUnregisteredUuid();
        $this->fhirCarePlanFixture->setId(new FHIRId());
        $payload = $this->fhirCarePlanFixture->jsonSerialize();
        $payload['subject'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRCarePlan($payload);

        $processingResult = $this->fhirCarePlanService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testUpdateReplacesRows(): void
    {
        $this->fhirCarePlanFixture->setId(new FHIRId());
        $insertResult = $this->fhirCarePlanService->insert($this->fhirCarePlanFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            "Insert should succeed: " . json_encode($insertResult->getValidationMessages())
        );

        $surrogateUuid = $this->firstDataRow($insertResult)['uuid'];
        $this->assertIsString($surrogateUuid);
        // CarePlanService::create() derives form_id in PHP (MAX(id) + 1), so it is an int.
        $formId = $this->firstDataRow($insertResult)['form_id'];
        $this->assertIsInt($formId);

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
        $this->assertIsNumeric($rowCount);
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
