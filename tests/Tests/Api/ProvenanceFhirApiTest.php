<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\RestControllers\FHIR\Trait\JsonResponseHandlerTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ProvenanceFhirApiTest extends TestCase
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
    }

    public function tearDown(): void
    {
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testGetOneResourceWithSurrogateKeyNotFound(): void
    {
        // we just want to execute the code
        // non-existant resource
        $resourceId = 'Patient-PSK-96506861-511f-4f6d-bc97-b65a78cf1996';
        $actualResult = $this->testClient->get("/apis/default/fhir/Provenance/" . $resourceId);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $actualResult->getStatusCode());
    }
}
