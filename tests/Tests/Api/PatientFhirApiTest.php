<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\RestControllers\FHIR\Trait\JsonResponseHandlerTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class PatientFhirApiTest extends TestCase
{
    use JsonResponseHandlerTrait;

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
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
}
