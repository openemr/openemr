<?php

/**
 * FHIR MedicationRequest API (HTTP write) tests.
 *
 * Drives real HTTP POST/PUT through OAuth against /apis/default/fhir/MedicationRequest
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
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class MedicationRequestFhirWriteApiTest extends TestCase
{
    private const RESOURCE_URL = '/apis/default/fhir/MedicationRequest';
    private const RESOURCE_TYPE = 'MedicationRequest';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    /** @var array<string, mixed> */
    private array $fhirFixture;
    private string $patientUuid;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

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

        $fixtureData = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/medication-request.json'),
            true
        );
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
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
        $this->fixtureManager->removeMedicationRequestFixtures();
        $this->fixtureManager->removePatientFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testPostCreatesMedicationRequest(): void
    {
        $response = $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture);
        $body = $response->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
            'POST ' . self::RESOURCE_URL . ' should return 201. Body: ' . $body
        );
        $contents = json_decode($body, true);
        $this->assertIsArray($contents, 'Create response should be a JSON object. Body: ' . $body);
        $this->assertArrayHasKey('uuid', $contents, 'Create response should carry the new resource uuid');
        $this->assertIsString($contents['uuid']);
    }

    public function testPutUpdatesMedicationRequest(): void
    {
        $createResponse = $this->testClient->post(self::RESOURCE_URL, $this->fhirFixture);
        $createBody = $createResponse->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $createResponse->getStatusCode(),
            'POST ' . self::RESOURCE_URL . ' should return 201. Body: ' . $createBody
        );
        $created = json_decode($createBody, true);
        $this->assertIsArray($created, 'Create response should be a JSON object. Body: ' . $createBody);
        $this->assertArrayHasKey('uuid', $created, 'Create response should carry the new resource id. Body: ' . $createBody);
        $id = $created['uuid'];
        $this->assertIsString($id);

        $updated = $this->fhirFixture;
        $updated['id'] = $id;
        $putResponse = $this->testClient->put(self::RESOURCE_URL, $id, $updated);
        $putBody = $putResponse->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_OK,
            $putResponse->getStatusCode(),
            'PUT ' . self::RESOURCE_URL . '/{id} should return 200. Body: ' . $putBody
        );
        // FhirServiceBase::update() re-shapes the stored row through parseOpenEMRRecord()
        // to build this body. A service that leaves that on FhirServiceBaseEmptyTrait
        // answers a successful PUT with null, which the status code alone does not reveal.
        $putContents = json_decode($putBody, true);
        $this->assertIsArray(
            $putContents,
            'PUT should answer with the updated resource, not a null body. Body: ' . $putBody
        );
        $this->assertSame(self::RESOURCE_TYPE, $putContents['resourceType'] ?? null);
        $this->assertSame($id, $putContents['id'] ?? null);
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
