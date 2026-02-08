<?php

namespace OpenEMR\Tests\Certification\HIT1\G10_Certification;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Api\BulkAPITestClient;
use OpenEMR\Tests\Certification\HIT1\G10_Certification\Trait\G10ApiTestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class BulkPatientExport311APITest extends TestCase
{
    use G10ApiTestTrait;

    //
    const DEFAULT_FHIR_GROUP_ID = "96509824-1cf5-4eb5-8107-700e24f26b14";
    // note inferno documentation has these as lowercase values, but they MUST be uppercase in the test inputs
    const ENCRYPTION_ALGORITHM_RS384 = 'RS384';
    const ENCRYPTION_ALGORITHM_ES384 = 'ES384';

    static protected string $previousProfileValue;

    /**
     * @return void
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setupG10Test();
    }

    public static function tearDownAfterClass(): void
    {
        self::teardownG10Test();
    }
//    private function getFakeTestGroupResponse() : array {
//        $response = file_get_contents(__DIR__ . '/../../../data/single_patient_api_test_run_20250624.json');
//        $response = json_decode($response, true);
//        return $response;
//    }

    /**
     * @return string
     * @throws GuzzleException
     */
    private function getInfernoJWKS(): \stdClass
    {
        if (!isset(self::$testClient)) {
            self::$testClient = new BulkAPITestClient(self::$baseUrl);
        }
            self::$testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
            $jwksResponse = self::$infernoClient->get('/custom/g10_certification/.well-known/jwks.json');
        if ($jwksResponse->getStatusCode() !== 200) {
            throw new RuntimeException("Failed to retrieve JWKS from Inferno. Status code: " . $jwksResponse->getStatusCode());
        }
            return json_decode($jwksResponse->getBody()->getContents());
    }
//    #[Test]
//    public function testPatientIds() {
//        self::$testClient = new BulkAPITestClient(self::$baseUrl);
//        self::$testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
//        $response = self::$testClient->get("/apis/default/fhir/Group/" . self::DEFAULT_FHIR_GROUP_ID);
//        $result = $response->getBody()->getContents();
//        $this->assertNotEmpty($result);
//        $data = json_decode($result, true);
//        $this->assertEquals(self::DEFAULT_FHIR_GROUP_ID, $data['id'], "Expected group ID to be " . self::DEFAULT_FHIR_GROUP_ID);
//        $this->assertEquals("Group", $data['resourceType'], "Expected resource type to be Group");
//        $this->assertCount(4, $data['member'], "Expected 3 members in the group");
//    }
    #[Test]
    /**
     * Test the Bulk Patient Export API functionality.
     *
     * This test will register a client, retrieve the JWKS, and run the bulk patient export tests.
     * It will assert that the results are not empty and that all tests pass.
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testBulkPatientExport(): void
    {
        // for now this uses the admin user to authenticate
        // TODO: @adunsulag need to implement this using a test practitioner user so we can test the inferno single patient API from a regular provider
        self::$testClient = new BulkAPITestClient(self::$baseUrl);
        $client = self::$testClient->registerClient(ApiTestClient::OPENEMR_AUTH_ENDPOINT, $this->getInfernoJWKS());
        $inputs = $this->getTestInputs($client->getIdentifier(), 'bulk_smart_auth_info');
        // we will use the filesystem to write out the test run settings so that we can see them in the code coverage report
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'multi_patient_api', $inputs);
        // useful for debugging the unit test...
//        $response = $this->getFakeTestGroupResponse();
        $this->assertNotEmpty($response['results'], "Test run results are empty for Bulk Patient Export API tests");
        // assert that the results are all passed
        $testsFailed = 0;
        $testsTotal = count($response['results']);
        foreach ($response['results'] as $result) {
            echo self::getDisplayName($result['test_id'] ?? $result['test_group_id']) . ": " . $result['result'] . "\n";
            // we have omit, skip, warn, etc we'll just key off fail at this point
            if ($result['result'] == 'fail') {
                $testsFailed += 1;
            }
        }
        if ($testsFailed > 0) {
            echo "Detailed Test Results:\n\n";
            $this->renderResults($response['results'], "Bulk Patient Export API tests did not pass");
        }
        $this->assertEquals(0, $testsFailed, "Bulk Patient Export API Test Failed.  Total tests failed " . $testsFailed . " out of " . $testsTotal . " tests run. Please see above for details.");
    }

    protected function getTestInputs(string $clientId, string $credentialsKeyName): array
    {
        // bulk_server_url
        // group_id
        // bulk_patient_ids_in_group (optional)
        // bulk_device_types_in_group (optional)
        // lines_to_validate (maximum number of resources to validate in the response)
        // bulk_timeout (defaults to 180 seconds, maximum 600 seconds)
        // bulk_smart_auth_info : AuthInfo
        //   auth_type: "backend_services" (required)
        //   use_discover: false (default false)
        //   token_url: string (required)
        //   jwks: (optional will use inferno default JWKS if not provided)
        //   encryption_algorithm (ES384[default] or RS384)
        $credentialsArray = [
            'token_url' => self::$baseUrl . self::$testClient::OAUTH_TOKEN_ENDPOINT
            , 'auth_type' => 'backend_services'
            , 'encryption_algorithm' => self::ENCRYPTION_ALGORITHM_RS384
            , 'requested_scopes' => BulkAPITestClient::SYSTEM_SCOPES
            , 'client_id' => $clientId
        ];
        return [
            ['name' => 'bulk_server_url', 'value' => self::$baseUrl . '/apis/default/fhir'],
            ['name' => 'bulk_timeout', 'value' => 180],
            ['name' => 'group_id', 'value' => self::DEFAULT_FHIR_GROUP_ID],
            ['name' => 'bulk_patient_ids_in_group', 'value' => self::PATIENT_IDS],
            ['name' => $credentialsKeyName, 'value' => $credentialsArray]
        ];
    }
}
