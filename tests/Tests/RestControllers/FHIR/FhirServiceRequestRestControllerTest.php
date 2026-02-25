<?php

/**
 * FHIR ServiceRequest REST Controller Integration Tests
 *
 * Tests the REST controller layer for ServiceRequest CRUD operations
 * with a real database connection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\RestControllers\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest;
use OpenEMR\RestControllers\FHIR\FhirServiceRequestRestController;
use OpenEMR\Tests\Fixtures\EncounterFixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use OpenEMR\Tests\RestControllers\FHIR\Trait\FhirResponseAssertionTrait;
use OpenEMR\Tests\RestControllers\FHIR\Trait\JsonResponseHandlerTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class FhirServiceRequestRestControllerTest extends TestCase
{
    use JsonResponseHandlerTrait;
    use FhirResponseAssertionTrait;

    private const LOG_LEVEL = Level::Emergency;

    private FhirServiceRequestRestController $controller;
    private EncounterFixtureManager $encounterFixtureManager;
    private PractitionerFixtureManager $practitionerFixtureManager;

    /** @var array<string, mixed> */
    private array $fhirFixture;

    private string $labUuid;
    private string $patientUuid;

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
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

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
            'subject' => ['reference' => "Patient/{$this->patientUuid}"],
            'encounter' => ['reference' => "Encounter/{$encounterUuid}"],
            'requester' => ['reference' => "Practitioner/{$practitionerUuid}"],
            'performer' => [['reference' => "Organization/{$this->labUuid}"]],
            'authoredOn' => '2026-01-15',
            'priority' => 'routine',
            'patientInstruction' => 'test-fixture-Collect morning sample',
            'note' => [['text' => 'test-fixture-Patient reports recent UTI symptoms']],
        ];

        $this->controller = new FhirServiceRequestRestController();
        $this->controller->setSystemLogger(new SystemLogger(self::LOG_LEVEL));
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

    public function testPost(): void
    {
        $actualResult = $this->controller->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_CREATED, $actualResult->getStatusCode());

        $contents = $this->getJsonContents($actualResult);
        $this->assertArrayHasKey('uuid', $contents);
        $this->assertNotEmpty($contents['uuid']);
    }

    public function testInvalidPost(): void
    {
        // Remove subject (required for patient_id) and performer (required for lab_id)
        unset($this->fhirFixture['subject']);
        unset($this->fhirFixture['performer']);

        $actualResult = $this->controller->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
    }

    public function testPatch(): void
    {
        $actualResult = $this->controller->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        /** @var string $fhirId */
        $fhirId = $contents['uuid'];

        $this->fhirFixture['priority'] = 'urgent';
        $actualResult = $this->controller->patch($fhirId, $this->fhirFixture);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());

        $contents = $this->getJsonContents($actualResult);
        $this->assertEquals($fhirId, $contents['id']);
    }

    public function testInvalidPatch(): void
    {
        $this->controller->post($this->fhirFixture);

        $actualResult = $this->controller->patch('bad-uuid', $this->fhirFixture);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());

        $contents = $this->getJsonContents($actualResult);
        /** @var array<string, mixed> $validationErrors */
        $validationErrors = $contents['validationErrors'];
        $this->assertGreaterThan(0, count($validationErrors));
    }

    public function testGetOne(): void
    {
        $actualResult = $this->controller->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        /** @var string $fhirId */
        $fhirId = $contents['uuid'];

        $actualResult = $this->controller->getOne($fhirId);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());

        $contents = $this->getJsonContents($actualResult);
        $this->assertNotEmpty($contents);
    }

    public function testGetAll(): void
    {
        $this->controller->post($this->fhirFixture);

        $searchParams = ['patient' => $this->patientUuid];
        $actualResult = $this->controller->getAll($searchParams);

        $fhirServiceRequest = new FHIRServiceRequest();
        $this->assertFhirBundleResponse($actualResult, Response::HTTP_OK, $fhirServiceRequest->get_fhirElementName());
    }
}
