<?php

/**
 * FHIR Goal API (HTTP write) tests.
 *
 * Drives real HTTP POST/PUT through OAuth against /apis/default/fhir/Goal
 * so routing, scope enforcement, and serialization are exercised end to end —
 * the path the service-layer CRUD tests bypass.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIREncounter;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class GoalFhirWriteApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/Goal';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    /** @var array<string, mixed> */
    private array $fhirFixture;
    private string $patientUuid;
    private string $encounterUuid;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $firstPatient = $patients[0];
        $this->assertIsArray($firstPatient);
        $patientRecord = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$firstPatient['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        // Goal writes anchor on an existing encounter via the encounter extension.
        $encounterRaw = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/encounter.json'),
            true
        );
        $this->assertIsArray($encounterRaw);
        $encounterPayload = $encounterRaw[0];
        $this->assertIsArray($encounterPayload);
        $encounterPayload['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $encounterResource = new FHIREncounter($encounterPayload);

        $encounterService = new FhirEncounterService();
        $encounterInsert = $encounterService->insert($encounterResource);
        $encounterInsertData = $encounterInsert->getData();
        $this->assertIsArray($encounterInsertData);
        $encounterData = $encounterInsertData[0];
        $this->assertIsArray($encounterData);
        $this->assertIsString($encounterData['euuid']);
        $this->encounterUuid = $encounterData['euuid'];

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/goal.json'),
            true
        );
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['extension'] = [[
            'url' => 'http://hl7.org/fhir/StructureDefinition/encounter-associatedEncounter',
            'valueReference' => ['reference' => 'Encounter/' . $this->encounterUuid],
        ]];
        unset($fixture['id']);
        $stringKeyedFixture = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $stringKeyedFixture[$key] = $value;
        }
        $this->fhirFixture = $stringKeyedFixture;
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeGoalFixtures();
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM form_encounter WHERE reason LIKE 'test-fixture%'"
        );
        $this->fixtureManager->removePatientFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPostCreatesGoal(): void
    {
        $response = $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture);
        $body = $response->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
            'POST ' . self::RESOURCE_URL . ' should return 201. Body: ' . $body
        );
        $contents = json_decode($body, true);
        $this->assertIsArray($contents);
        $this->assertArrayHasKey('uuid', $contents, 'Create response should carry the new resource uuid');
        $this->assertIsString($contents['uuid']);
    }

    public function testPutUpdatesGoal(): void
    {
        $created = json_decode(
            $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture)->getBody()->getContents(),
            true
        );
        $this->assertIsArray($created);
        $this->assertArrayHasKey('uuid', $created);
        $id = $created['uuid'];
        $this->assertIsString($id);

        $updated = $this->fhirFixture;
        $updated['id'] = $id;
        $putResponse = $this->testClient->put(self::RESOURCE_URL, $id, $updated);
        $this->assertSame(
            Response::HTTP_OK,
            $putResponse->getStatusCode(),
            'PUT ' . self::RESOURCE_URL . '/{id} should return 200. Body: ' . $putResponse->getBody()->getContents()
        );
    }

    public function testPostWithoutSubjectReturnsError(): void
    {
        $invalid = $this->fhirFixture;
        unset($invalid['subject']);
        $response = $this->testClient->post(self::RESOURCE_URL, $invalid);
        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
            'POST without subject should return 400. Body: ' . $response->getBody()->getContents()
        );
    }
}
