<?php

/**
 * HTTP search and read tests for the FHIR Immunization resource.
 *
 * ImmunizationFhirWriteApiTest covers POST and PUT; this covers GET /fhir/Immunization and
 * GET /fhir/Immunization/:uuid. An influenza immunization is seeded through the API in setUp,
 * the same way the write test does it, and is the immunization every test looks for.
 *
 * Reading by id and searching by _id both go through the uuid column, so they are tested
 * separately.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Fariha Batool <farihabatool2023@gmail.com>
 * @copyright Copyright (c) 2026 Fariha Batool <farihabatool2023@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class ImmunizationFhirApiTest extends TestCase
{
    private const ENDPOINT = '/apis/default/fhir/Immunization';
    private const ABSENT_UUID = '11111111-1111-1111-1111-111111111111';
    private const CVX_SYSTEM = 'http://hl7.org/fhir/sid/cvx';
    private const INFLUENZA_CVX = '197';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private string $patientUuid;
    private string $immunizationUuid;

    /**
     * Seeds a patient and an influenza immunization, and gets an API token.
     */
    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patient = $patients[0];
        $this->assertIsArray($patient);
        $record = QueryUtils::querySingleRow(
            'SELECT uuid FROM patient_data WHERE pubpid = ?',
            [$patient['pubpid']]
        );
        $this->assertIsArray($record);
        $this->patientUuid = UuidRegistry::uuidToString($record['uuid']);

        $fixtures = json_decode(
            (string) file_get_contents(__DIR__ . '/../Fixtures/FHIR/immunization.json'),
            true
        );
        $this->assertIsArray($fixtures);
        $fixture = $fixtures[0];
        $this->assertIsArray($fixture);
        $fixture['patient'] = ['reference' => 'Patient/' . $this->patientUuid];
        unset($fixture['id']);
        $body = [];
        foreach ($fixture as $key => $value) {
            $this->assertIsString($key);
            $body[$key] = $value;
        }

        $response = $this->testClient->post(self::ENDPOINT, $body);
        $responseBody = $response->getBody()->getContents();
        $this->assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
            'Seeding the immunization should return 201. Body: ' . $responseBody
        );
        $created = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($created);
        $uuid = $created['uuid'] ?? null;
        $this->assertIsString($uuid);
        $this->immunizationUuid = $uuid;
    }

    /**
     * Removes the seeded immunization and patient, then revokes the token.
     */
    protected function tearDown(): void
    {
        // PHPUnit runs tearDown() even when setUp() fails part way, so every property is
        // checked before it is used.
        try {
            // This run's immunization only, so parallel workers keep theirs.
            if (isset($this->immunizationUuid)) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM immunizations WHERE uuid = ?',
                    [UuidRegistry::uuidToBytes($this->immunizationUuid)]
                );
            }
            if (isset($this->fixtureManager)) {
                $this->fixtureManager->removePatientFixtures();
            }
        } finally {
            if (isset($this->testClient)) {
                try {
                    $this->testClient->cleanupRevokeAuth();
                } finally {
                    $this->testClient->cleanupClient();
                }
            }
        }
    }

    /**
     * A patient search returns the seeded immunization.
     */
    public function testSearchByPatientIncludesSeededImmunization(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['patient' => $this->patientUuid])
        );
        $entries = $bundle['entry'] ?? null;
        $this->assertIsArray($entries);
        $found = false;
        foreach ($entries as $entry) {
            $this->assertIsArray($entry);
            $resource = $entry['resource'] ?? null;
            $this->assertIsArray($resource);
            $this->assertSame('Immunization', $resource['resourceType'] ?? null);
            if (($resource['id'] ?? null) === $this->immunizationUuid) {
                $this->assertSeededImmunization($resource);
                $found = true;
            }
        }
        $this->assertTrue($found, 'The search must include the seeded immunization');
    }

    /**
     * An _id search returns only the seeded immunization.
     *
     * This checks that every entry is the seeded immunization, not how many entries there are.
     * ImmunizationService joins the codes table on the code number alone, without a code_type,
     * so when another code system also has the number 197 the same immunization comes back more
     * than once. That is a separate defect from the _id filter this test is about.
     */
    public function testSearchByIdReturnsOnlySeededImmunization(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['_id' => $this->immunizationUuid])
        );
        $entries = $bundle['entry'] ?? null;
        $this->assertIsArray($entries);
        $this->assertNotEmpty($entries);
        foreach ($entries as $entry) {
            $this->assertIsArray($entry);
            $resource = $entry['resource'] ?? null;
            $this->assertIsArray($resource);
            $this->assertSeededImmunization($resource);
        }
    }

    /**
     * Reading by uuid returns the seeded immunization.
     */
    public function testReadReturnsSeededImmunization(): void
    {
        $response = $this->testClient->getOne(self::ENDPOINT, $this->immunizationUuid);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSeededImmunization($this->decodeJson($response));
    }

    /**
     * An _id search for an absent uuid returns an empty collection Bundle.
     */
    public function testSearchWithNoMatchReturnsEmptyBundle(): void
    {
        $bundle = $this->assertCollectionBundle(
            $this->testClient->get(self::ENDPOINT, ['_id' => self::ABSENT_UUID])
        );
        $this->assertSame([], $bundle['entry'] ?? []);
    }

    /**
     * Reading an absent uuid returns 404 with an empty JSON array.
     */
    public function testReadAbsentUuidReturnsNotFound(): void
    {
        $response = $this->testClient->getOne(self::ENDPOINT, self::ABSENT_UUID);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame([], $this->decodeJson($response));
    }

    /**
     * Searching without a token returns 401.
     */
    public function testSearchWithoutTokenReturnsUnauthorized(): void
    {
        $client = new ApiTestClient(self::baseUrl(), false);
        $this->assertUnauthorized($client->get(self::ENDPOINT));
    }

    /**
     * Reading without a token returns 401.
     */
    public function testReadWithoutTokenReturnsUnauthorized(): void
    {
        $client = new ApiTestClient(self::baseUrl(), false);
        $this->assertUnauthorized($client->getOne(self::ENDPOINT, $this->immunizationUuid));
    }

    /**
     * Asserts the seeded influenza immunization: id, status, patient, CVX code, date, lot,
     * expiration and note.
     *
     * @param array<array-key, mixed> $resource
     */
    private function assertSeededImmunization(array $resource): void
    {
        $this->assertSame('Immunization', $resource['resourceType'] ?? null);
        $this->assertSame($this->immunizationUuid, $resource['id'] ?? null);
        $this->assertSame('completed', $resource['status'] ?? null);

        $patient = $resource['patient'] ?? null;
        $this->assertIsArray($patient);
        $this->assertSame('Patient/' . $this->patientUuid, $patient['reference'] ?? null);

        $vaccineCode = $resource['vaccineCode'] ?? null;
        $this->assertIsArray($vaccineCode);
        $codings = $vaccineCode['coding'] ?? null;
        $this->assertIsArray($codings);
        $cvxCodes = [];
        foreach ($codings as $coding) {
            $this->assertIsArray($coding);
            if (($coding['system'] ?? null) === self::CVX_SYSTEM) {
                $cvxCodes[] = $coding['code'] ?? null;
            }
        }
        $this->assertContains(self::INFLUENZA_CVX, $cvxCodes);

        $occurrence = $resource['occurrenceDateTime'] ?? null;
        $this->assertIsString($occurrence);
        $this->assertStringStartsWith('2021-01-10', $occurrence);

        $this->assertSame('AAJN11K', $resource['lotNumber'] ?? null);

        $expiration = $resource['expirationDate'] ?? null;
        $this->assertIsString($expiration);
        $this->assertStringStartsWith('2099-02-15', $expiration);

        $notes = $resource['note'] ?? null;
        $this->assertIsArray($notes);
        $note = $notes[0] ?? null;
        $this->assertIsArray($note);
        $this->assertSame('test-fixture immunization for influenza', $note['text'] ?? null);
    }

    /**
     * Asserts a 200 collection Bundle and returns it decoded.
     *
     * @return array<array-key, mixed>
     */
    private function assertCollectionBundle(ResponseInterface $response): array
    {
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $bundle = $this->decodeJson($response);
        $this->assertSame('Bundle', $bundle['resourceType'] ?? null);
        $this->assertSame('collection', $bundle['type'] ?? null);
        return $bundle;
    }

    /**
     * Asserts the 401 body the OAuth layer returns, not a FHIR OperationOutcome.
     */
    private function assertUnauthorized(ResponseInterface $response): void
    {
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $body = $this->decodeJson($response);
        $this->assertArrayHasKey('error', $body);
        $message = $body['message'] ?? null;
        $this->assertIsString($message);
        $this->assertStringContainsString('denied the request', $message);
        $this->assertArrayNotHasKey('resourceType', $body);
    }

    /**
     * Decodes a JSON response body into an array.
     *
     * @return array<array-key, mixed>
     */
    private function decodeJson(ResponseInterface $response): array
    {
        $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($body);
        return $body;
    }

    /**
     * Returns the API base URL from OPENEMR_BASE_URL_API, or https://localhost.
     */
    private static function baseUrl(): string
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true);
        return is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : 'https://localhost';
    }
}
