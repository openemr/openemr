<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationExportRestController;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\RestControllers\FHIR\Trait\JsonResponseHandlerTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class GroupExportFhirApiTest extends TestCase
{
    use JsonResponseHandlerTrait;

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private array $fhirFixture;
    private FhirPatientRestController $fhirPatientController;
    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "http://localhost";
        $this->testClient = new BulkAPITestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    public function tearDown(): void
    {
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    public function testGroupExportWithNonExistingGroupId(): void
    {
        // we just want to execute the code
        // non-existant group, but make sure the route exists
        $groupId = '99999999-511f-4f6d-bc97-b65a78cf1996';

        $this->testClient->setHeaders([
            'Accept' => FhirOperationExportRestController::ACCEPT_HEADER_OPERATION_OUTCOME,
            'Prefer' => FhirOperationExportRestController::PREFER_HEADER,
            'Authorization' => 'Bearer ' . $this->testClient->getAccessToken()
        ]);
        $actualResult = $this->testClient->get("/apis/default/fhir/Group/" . $groupId . '/$export');
        $this->assertEquals(Response::HTTP_ACCEPTED, $actualResult->getStatusCode());
        $this->assertNotEmpty($actualResult->getHeaders()['Content-Location'][0], "Content-Location header should be populatd");
        // because everything happens synchronously in this test, we can check the content location
        $request =  HttpRestRequest::create($actualResult->getHeaders()['Content-Location'][0]);
        $this->assertStringEndsWith('/fhir/$bulkdata-status', $request->getPathInfo(), 'Content-Location should end with /fhir/$bulkdata-status');
        $this->assertNotEmpty($request->query->get('job'), "Job query parameter should be present in the Content-Location URL");
        $bulkStatusResponse = $this->testClient->get($request->getPathInfo(), ['job' => $request->query->get('job')]);
        $this->assertEquals(Response::HTTP_OK, $bulkStatusResponse->getStatusCode(), "Bulk status should be OK");
        $bulkStatusData = json_decode($bulkStatusResponse->getBody()->getContents(), true);
        $this->assertNotEmpty($bulkStatusData, "Bulk status data should not be empty");
    }
    // TODO: @adunsulag need to fix a bulkdata-status test where there is no job id.  Should not throw a 500 error.
}
