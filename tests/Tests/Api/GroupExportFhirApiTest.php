<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
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
    private ?string $savedSiteAddrOath = null;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new BulkAPITestClient($baseUrl, false);

        // Align site_addr_oath with $baseUrl so the JWT client-assertion's
        // 'aud' claim matches the server's expected audience. The dev
        // compose sets site_addr_oath to an external host:port URL
        // (e.g. https://localhost:9302) for browser access, but tests run
        // inside the container where the OAuth server resolves to
        // https://localhost. Without this, the token endpoint rejects
        // the assertion as invalid_client (HTTP 400 from
        // JWTClientAuthenticationService::validateJWTClientAssertion);
        // setAuthToken's response is not 200 so $this->access_token stays
        // null; the subsequent FHIR request goes out with an empty bearer
        // and the resource server returns 401. CI's site_addr_oath
        // happens to match the container's view already so this is a
        // no-op there.
        $row = QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'site_addr_oath'");
        $value = is_array($row) ? ($row['gl_value'] ?? null) : null;
        $this->savedSiteAddrOath = is_string($value) ? $value : null;
        QueryUtils::sqlStatementThrowException("UPDATE globals SET gl_value = ? WHERE gl_name = 'site_addr_oath'", [$baseUrl]);
    }

    public function tearDown(): void
    {
        // try/finally so the site_addr_oath restore always runs even if a
        // cleanup HTTP call throws (Guzzle can raise on connection errors).
        // Otherwise the override would leak into later tests in the same
        // PHPUnit process.
        try {
            $this->testClient->cleanupRevokeAuth();
            $this->testClient->cleanupClient();
        } finally {
            if ($this->savedSiteAddrOath !== null) {
                QueryUtils::sqlStatementThrowException("UPDATE globals SET gl_value = ? WHERE gl_name = 'site_addr_oath'", [$this->savedSiteAddrOath]);
                $this->savedSiteAddrOath = null;
            }
        }
    }

    public function testGroupExportWithNonExistingGroupId(): void
    {
        $this->testClient->setAuthToken(BulkAPITestClient::OPENEMR_AUTH_ENDPOINT);
        // the audience and internal oauth server url need to match, if the internal oauth server is not the same as the external
        // phpunit endpoint location we are going to have issues with the test client not being able to get an access token.
//        $this->markTestIncomplete("Incomplete until we figure out how to deal with localhost differences in test environments and inferno environments.");
//        // we just want to execute the code
//        // non-existent group, but make sure the route exists
        $groupId = '99999999-511f-4f6d-bc97-b65a78cf1996';
//
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
        $job = $request->query->get('job');
        $bulkStatusResponse = $this->testClient->get($request->getPathInfo(), ['job' => $job]);
        $this->assertEquals(Response::HTTP_OK, $bulkStatusResponse->getStatusCode(), "Bulk status should be OK");
        $bulkStatusData = json_decode((string) $bulkStatusResponse->getBody()->getContents(), true);
        $this->assertNotEmpty($bulkStatusData, "Bulk status data should not be empty");
    }
    // TODO: @adunsulag need to fix a bulkdata-status test where there is no job id.  Should not throw a 500 error.
}
