<?php

/**
 * FHIR ServiceRequest Service CRUD Integration Tests
 *
 * Tests insert and update operations through the FhirServiceRequestService layer
 * with a real database connection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirServiceRequestService;
use OpenEMR\Services\FHIR\Serialization\FhirServiceRequestSerializer;
use OpenEMR\Tests\Fixtures\EncounterFixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use PHPUnit\Framework\TestCase;

class FhirServiceRequestServiceCrudTest extends TestCase
{
    private EncounterFixtureManager $encounterFixtureManager;
    private PractitionerFixtureManager $practitionerFixtureManager;
    private FhirServiceRequestService $fhirService;

    /** @var array<string, mixed> */
    private array $fhirFixture;

    private string $labUuid;

    protected function setUp(): void
    {
        $this->encounterFixtureManager = new EncounterFixtureManager();
        $this->encounterFixtureManager->installFixtures();

        $this->practitionerFixtureManager = new PractitionerFixtureManager();
        $this->practitionerFixtureManager->installPractitionerFixtures();

        // Create a procedure_providers record for the performer/lab_id reference
        $labUuidBytes = (new UuidRegistry(['table_name' => 'procedure_providers']))->createUuid();
        $this->labUuid = UuidRegistry::uuidToString($labUuidBytes);
        QueryUtils::sqlInsert(
            "INSERT INTO procedure_providers SET uuid = ?, name = 'test-fixture-Lab'",
            [$labUuidBytes]
        );

        // Resolve fixture UUIDs
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            ['test-fixture-789456']
        );
        $this->assertIsArray($patientRecord, 'Patient fixture not found');
        $patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $encounterRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM form_encounter WHERE reason LIKE 'test-fixture-%' LIMIT 1",
            []
        );
        $this->assertIsArray($encounterRecord, 'Encounter fixture not found');
        $encounterUuid = UuidRegistry::uuidToString($encounterRecord['uuid']);

        $practitionerRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM users WHERE fname LIKE 'test-fixture-%' LIMIT 1",
            []
        );
        $this->assertIsArray($practitionerRecord, 'Practitioner fixture not found');
        $practitionerUuid = UuidRegistry::uuidToString($practitionerRecord['uuid']);

        $this->fhirFixture = [
            'resourceType' => 'ServiceRequest',
            'status' => 'active',
            'intent' => 'order',
            'category' => [
                ['coding' => [['system' => 'http://snomed.info/sct', 'code' => '108252007', 'display' => 'Laboratory procedure']]],
            ],
            'code' => [
                'coding' => [['system' => 'http://loinc.org', 'code' => '24356-8', 'display' => 'Urinalysis complete']],
                'text' => 'Urinalysis complete',
            ],
            'subject' => ['reference' => "Patient/{$patientUuid}"],
            'encounter' => ['reference' => "Encounter/{$encounterUuid}"],
            'requester' => ['reference' => "Practitioner/{$practitionerUuid}"],
            'performer' => [['reference' => "Organization/{$this->labUuid}"]],
            'authoredOn' => '2026-01-15',
            'priority' => 'routine',
            'patientInstruction' => 'test-fixture-Collect morning sample',
            'note' => [['text' => 'test-fixture-Patient reports recent UTI symptoms']],
        ];

        $this->fhirService = new FhirServiceRequestService();
        $this->fhirService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        // Delete procedure_order_code entries (foreign key child)
        QueryUtils::sqlStatementThrowException(
            "DELETE poc FROM procedure_order_code poc"
            . " INNER JOIN procedure_order po ON poc.procedure_order_id = po.procedure_order_id"
            . " WHERE po.clinical_hx LIKE 'test-fixture-%'",
            []
        );

        // Delete procedure_order entries
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM procedure_order WHERE clinical_hx LIKE 'test-fixture-%'",
            []
        );

        // Delete test procedure_providers record
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM procedure_providers WHERE name = 'test-fixture-Lab'",
            []
        );

        $this->encounterFixtureManager->removeFixtures();
        $this->practitionerFixtureManager->removePractitionerFixtures();
    }

    public function testInsert(): void
    {
        $serviceRequest = FhirServiceRequestSerializer::deserialize($this->fhirFixture);
        $processingResult = $this->fhirService->insert($serviceRequest);

        $this->assertTrue($processingResult->isValid(), 'Insert should succeed: '
            . json_encode($processingResult->getValidationMessages()));

        /** @var list<array{uuid: string, id: int}> $insertData */
        $insertData = $processingResult->getData();
        $dataResult = $insertData[0];
        $this->assertNotEmpty($dataResult['uuid']);
        $this->assertGreaterThan(0, $dataResult['id']);
    }

    public function testInsertRoundTrip(): void
    {
        // Insert
        $serviceRequest = FhirServiceRequestSerializer::deserialize($this->fhirFixture);
        $processingResult = $this->fhirService->insert($serviceRequest);
        $this->assertTrue($processingResult->isValid(), 'Insert should succeed: '
            . json_encode($processingResult->getValidationMessages()));

        /** @var list<array{uuid: string, id: int}> $insertData */
        $insertData = $processingResult->getData();
        $fhirId = $insertData[0]['uuid'];

        // Read back
        $getResult = $this->fhirService->getOne($fhirId);
        $this->assertTrue($getResult->isValid(), 'getOne should succeed');

        /** @var list<FHIRServiceRequest> $getData */
        $getData = $getResult->getData();
        $this->assertCount(1, $getData);
        $returned = $getData[0];

        // Verify key fields survived the write→read round-trip
        $this->assertEquals($fhirId, (string) $returned->getId());
        $this->assertEquals('active', (string) $returned->getStatus());
        $this->assertEquals('order', (string) $returned->getIntent());
        $this->assertEquals('routine', (string) $returned->getPriority());

        // Subject reference preserved
        $subjectRef = (string) $returned->getSubject()->getReference();
        $this->assertStringContainsString('Patient/', $subjectRef);

        // Encounter reference preserved
        $encounterRef = (string) $returned->getEncounter()->getReference();
        $this->assertStringContainsString('Encounter/', $encounterRef);

        // Requester reference preserved
        $requesterRef = (string) $returned->getRequester()->getReference();
        $this->assertStringContainsString('Practitioner/', $requesterRef);

        // Category preserved (laboratory)
        $categories = $returned->getCategory();
        $this->assertNotEmpty($categories);

        // Patient instruction preserved
        $this->assertEquals(
            'test-fixture-Collect morning sample',
            (string) $returned->getPatientInstruction()
        );
    }

    public function testInsertWithErrors(): void
    {
        // Remove subject (required field for patient_id resolution)
        unset($this->fhirFixture['subject']);
        // Remove performer (required lab_id)
        unset($this->fhirFixture['performer']);

        $serviceRequest = FhirServiceRequestSerializer::deserialize($this->fhirFixture);
        $processingResult = $this->fhirService->insert($serviceRequest);

        $this->assertFalse($processingResult->isValid());
        /** @var list<mixed> $errorData */
        $errorData = $processingResult->getData();
        $this->assertCount(0, $errorData);
    }

    public function testUpdate(): void
    {
        // Insert first
        $serviceRequest = FhirServiceRequestSerializer::deserialize($this->fhirFixture);
        $processingResult = $this->fhirService->insert($serviceRequest);
        $this->assertTrue($processingResult->isValid(), 'Insert should succeed: '
            . json_encode($processingResult->getValidationMessages()));

        /** @var list<array{uuid: string, id: int}> $insertData */
        $insertData = $processingResult->getData();
        $fhirId = $insertData[0]['uuid'];

        // Update with new priority
        $this->fhirFixture['priority'] = 'urgent';
        $updatedServiceRequest = FhirServiceRequestSerializer::deserialize($this->fhirFixture);
        $updatedServiceRequest->setId(new FHIRId($fhirId));

        $updateResult = $this->fhirService->update($fhirId, $updatedServiceRequest);
        $this->assertTrue($updateResult->isValid(), 'Update should succeed: '
            . json_encode($updateResult->getValidationMessages()));

        /** @var list<FHIRServiceRequest> $updateData */
        $updateData = $updateResult->getData();
        $this->assertEquals($fhirId, (string) $updateData[0]->getId());
    }

    public function testUpdateWithErrors(): void
    {
        $serviceRequest = FhirServiceRequestSerializer::deserialize($this->fhirFixture);
        $updateResult = $this->fhirService->update('bad-uuid', $serviceRequest);

        $this->assertFalse($updateResult->isValid());
        /** @var array<string, mixed> $validationMessages */
        $validationMessages = $updateResult->getValidationMessages();
        $this->assertGreaterThan(0, count($validationMessages));
        /** @var list<mixed> $updateErrorData */
        $updateErrorData = $updateResult->getData();
        $this->assertCount(0, $updateErrorData);
    }
}
