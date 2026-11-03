<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\RestControllers\FHIR\Trait\JsonResponseHandlerTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * GET/PUT /fhir/Patient/:uuid HTTP-level coverage (see #12343), added to the pre-existing
 * search tests below.
 *
 * OpenEMR vs FHIR conventions pinned by these tests (server behavior, not the spec):
 * - Read-one errors return validationErrors (400) for a malformed uuid, or an empty JSON
 *   array (404) for an unknown-but-well-formed one -- same shape as every other FHIR
 *   read-one route in this codebase (RestControllerHelper::handleFhirProcessingResult()).
 * - PUT against an unknown OR a malformed uuid both return 400 with validationErrors, never
 *   404: PatientValidator's DATABASE_UPDATE_CONTEXT requires the path uuid to already exist
 *   (PatientValidator::isExistingUuid()), and a malformed uuid fails that same existence
 *   check (UuidRegistry::uuidToBytes() throws, caught, treated as "does not exist").
 */
class PatientFhirApiTest extends TestCase
{
    use JsonResponseHandlerTrait;

    private const ENDPOINT = "/apis/default/fhir/Patient";

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    /** @var array<string, mixed> */
    private array $fhirFixture;
    private FhirPatientRestController $fhirPatientController;
    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
//        $this->baseUrl = $baseUrl;
//        $this->oauthBaseUrl = $baseUrl . self::CAPABILITY_OAUTH_PREFIX;

        $this->fixtureManager = new FixtureManager();
        $this->fhirFixture = (array) $this->fixtureManager->getSingleFhirPatientFixture();
        $this->fhirPatientController = new FhirPatientRestController();
        unset($this->fhirFixture['id']);
        unset($this->fhirFixture['meta']);
    }

    public function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testGetBy_id(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_CREATED, $actualResult->getStatusCode(), "FHIR Patient post should have returned a 201 Created response");
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'];

        $actualResult = $this->testClient->get("/apis/default/fhir/Patient", ['_id' => $fhirId]);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());
        $body = $actualResult->getBody()->getContents();
        $this->assertNotEmpty($body, "Patient search by _id should have returned a result");
        $contents = json_decode((string) $body, true);
        $this->assertArrayhasKey("entry", $contents);
        $this->assertNotEmpty($contents['entry'], "Patient search by _id should have returned a result");
        $this->assertCount(1, $contents['entry'], "Patient search by _id should have returned a single result");
        $this->assertArrayHasKey("resource", $contents['entry'][0]);
        $this->assertArrayHasKey("id", $contents['entry'][0]['resource']);
        $this->assertEquals($fhirId, $contents['entry'][0]['resource']['id'], "Patient search by _id should have returned the correct patient");
    }

    public function testSearchByPostParameter(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'];

        // Assuming the fixture has a postal code set
        $postalCode = $this->fhirFixture['address'][0]['postalCode'] ?? '12345';

        $actualResult = $this->testClient->post("/apis/default/fhir/Patient/_search", ['_id' => $fhirId], false);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());
        $body = $actualResult->getBody()->getContents();
        $this->assertNotEmpty($body, "Patient search by postal code should have returned a result");
        $contents = json_decode($body, true);
        $this->assertArrayhasKey("entry", $contents);
        $this->assertNotEmpty($contents['entry'], "Patient search by postal code should have returned a result");
        foreach ($contents['entry'] as $entry) {
            if ($entry['resource']['id'] === $fhirId) {
                return; // Found the patient with the correct ID
            }
        }
        $this->fail("Patient search by postal code did not return the expected patient");
    }

    // ---------------------------------------------------------------------
    // Read one
    // ---------------------------------------------------------------------

    /**
     * Reading the seeded patient by uuid over HTTP should return the full Patient resource.
     */
    public function testGetOneReturnsSeededPatient(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'] ?? null;
        $this->assertIsString($fhirId);

        $result = $this->testClient->getOne(self::ENDPOINT, $fhirId);
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());

        $resource = $this->decodeJsonArray($result);
        $this->assertSame("Patient", $resource['resourceType'] ?? null);
        $this->assertSame($fhirId, $resource['id'] ?? null);

        $expectedNames = $this->fhirFixture['name'] ?? null;
        $this->assertIsArray($expectedNames);
        $expectedFirstName = $expectedNames[0] ?? null;
        $this->assertIsArray($expectedFirstName);

        $actualNames = $resource['name'] ?? null;
        $this->assertIsArray($actualNames);
        $actualFirstName = $actualNames[0] ?? null;
        $this->assertIsArray($actualFirstName);
        $this->assertSame($expectedFirstName['family'] ?? null, $actualFirstName['family'] ?? null);
    }

    /**
     * A well-formed but unknown uuid returns 404 with an empty JSON array (not
     * OperationOutcome), same shape as every other FHIR read-one route in this codebase.
     */
    public function testGetOneWithUnknownUuidReturnsNotFound(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, "11111111-1111-1111-1111-111111111111");
        $this->assertEquals(Response::HTTP_NOT_FOUND, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertEmpty($body, "404 body should be an empty JSON array");
    }

    /**
     * A malformed uuid produces 400 with validationErrors (not OperationOutcome, not 404).
     */
    public function testGetOneWithMalformedUuidReturnsBadRequest(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, "not-a-uuid");
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertNotEmpty($body['validationErrors']);
        $this->assertArrayNotHasKey('resourceType', $body, "400 body should not be a FHIR resource");
    }

    // ---------------------------------------------------------------------
    // Update
    // ---------------------------------------------------------------------

    /**
     * PUT with a full, valid Patient resource updates the record; the new value is visible
     * on a following GET.
     */
    public function testPutUpdatesFieldVisibleOnFollowingGet(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'] ?? null;
        $this->assertIsString($fhirId);

        $updatedFixture = $this->fixtureWithFamilyName('UpdatedFamilyName');

        $putResult = $this->testClient->put(self::ENDPOINT, $fhirId, $updatedFixture);
        $this->assertEquals(Response::HTTP_OK, $putResult->getStatusCode());

        $getResult = $this->testClient->getOne(self::ENDPOINT, $fhirId);
        $this->assertEquals(Response::HTTP_OK, $getResult->getStatusCode());

        $resource = $this->decodeJsonArray($getResult);
        $actualNames = $resource['name'] ?? null;
        $this->assertIsArray($actualNames);
        $actualFirstName = $actualNames[0] ?? null;
        $this->assertIsArray($actualFirstName);
        $this->assertSame('UpdatedFamilyName', $actualFirstName['family'] ?? null);
    }

    /**
     * PUT against an unknown (but well-formed) uuid returns 400 with validationErrors, not
     * 404: PatientValidator's DATABASE_UPDATE_CONTEXT requires the path uuid to already
     * exist, so an update to a nonexistent patient fails validation rather than falling
     * through to a not-found response.
     */
    public function testPutWithUnknownUuidReturnsBadRequest(): void
    {
        $updatedFixture = $this->fixtureWithFamilyName('UpdatedFamilyName');

        $result = $this->testClient->put(self::ENDPOINT, "11111111-1111-1111-1111-111111111111", $updatedFixture);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertNotEmpty($body['validationErrors']);
    }

    /**
     * PUT against a malformed uuid returns the same 400 with validationErrors as an unknown
     * uuid: the path uuid fails PatientValidator::isExistingUuid() either way, because
     * UuidRegistry::uuidToBytes() throws on a malformed value and the throw is caught and
     * treated as "does not exist".
     */
    public function testPutWithMalformedUuidReturnsBadRequest(): void
    {
        $updatedFixture = $this->fixtureWithFamilyName('UpdatedFamilyName');

        $result = $this->testClient->put(self::ENDPOINT, "not-a-uuid", $updatedFixture);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertNotEmpty($body['validationErrors']);
        $this->assertArrayNotHasKey('resourceType', $body, "400 body should not be a FHIR resource");
    }

    // ---------------------------------------------------------------------
    // Authorization
    // ---------------------------------------------------------------------

    /**
     * A request without a bearer token is rejected with 401 and an OAuth denial body.
     */
    public function testGetOneUnauthorizedReturns401(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $unauthClient = new ApiTestClient($baseUrl, false);
        // Deliberately do NOT call setAuthToken().
        $result = $unauthClient->getOne(self::ENDPOINT, "11111111-1111-1111-1111-111111111111");
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertArrayHasKey('message', $body);
        $message = $body['message'];
        $this->assertIsString($message);
        $this->assertStringContainsString(
            'denied the request',
            $message,
            '401 should be an OAuth authorization denial, not a routing or server error'
        );
        $this->assertArrayNotHasKey('resourceType', $body, "401 body should not be a FHIR resource");
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Decode a JSON body from an HTTP (Guzzle) response and assert it is an array.
     *
     * @return array<array-key, mixed>
     */
    private function decodeJsonArray(ResponseInterface $response): array
    {
        $body = (string) $response->getBody()->getContents();
        $this->assertNotEmpty($body, "Response should have a JSON body");
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded, "Response body should be JSON");
        return $decoded;
    }

    /**
     * Build a full Patient resource body from the fixture, with name[0].family replaced --
     * a valid PUT payload for testing update behavior.
     *
     * @return array<string, mixed>
     */
    private function fixtureWithFamilyName(string $family): array
    {
        $updated = $this->fhirFixture;

        $names = $updated['name'] ?? null;
        $this->assertIsArray($names);
        $firstName = $names[0] ?? null;
        $this->assertIsArray($firstName);
        $firstName['family'] = $family;
        $names[0] = $firstName;
        $updated['name'] = $names;
        return $updated;
    }
}
