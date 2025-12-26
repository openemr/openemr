<?php

namespace OpenEMR\Tests\Certification\HIT1\G10_Certification\Trait;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Tests\Api\ApiTestClient;
use Exception;

trait G10ApiTestTrait
{
    /**
     * @var string The previous FHIR profile version that was used in the globals for the max supported fhir version
     */
    static protected string $previousProfileValue;

    // Alice Jones (96506861-511f-4f6d-bc97-b65a78cf1995),
    // Jeremy Bates (96891ab2-01ad-49f9-9958-cdad71bd33c1),
    // Happy Child(968944d0-180a-48ac-8049-636ae8ac6127),
    // and John Appleseed (969f72c3-0256-488e-b25b-8fff3af18b1b)
    // are the patients used in the Inferno Single Patient API tests.
    const PATIENT_IDS = '96506861-511f-4f6d-bc97-b65a78cf1995,96891ab2-01ad-49f9-9958-cdad71bd33c1,968944d0-180a-48ac-8049-636ae8ac6127,969f72c3-0256-488e-b25b-8fff3af18b1b';
    const PATIENT_ID_PRIMARY = '96506861-511f-4f6d-bc97-b65a78cf1995';

    // we break them apart due to some wierdisness in the Inferno Single Patient API tests
    const ADDITIONAL_PATIENT_IDS = '96891ab2-01ad-49f9-9958-cdad71bd33c1,968944d0-180a-48ac-8049-636ae8ac6127';

    const TEST_SUITE_US_CORE_V311 = 'us_core_v311'; // this is the test suite id for the US Core v3.11 tests
    const TEST_SUITE_G10_CERTIFICATION = 'g10_certification'; // this is the test suite id for the US Core v3.11 certification tests

    const TEST_SUITE = self::TEST_SUITE_G10_CERTIFICATION;
    const DEFAULT_OPENEMR_BASE_URL_API = 'http://openemr';
    const DEFAULT_INFERNO_BASE_URL = 'http://nginx';
    const DEFAULT_TEST_GROUP_ID = 'us_core_v311-us_core_v311_fhir_api';



    private static ApiTestClient $testClient;
    private static string $baseUrl;

    private static Client $infernoClient;

    private static ?string $sessionId;

    const MAX_POLLING_ATTEMPTS = 200;

    const POLLING_INTERVAL = 5; // seconds

    static array $suiteResponse;

    static array $idMap = [];

    public static function setupG10Test(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: self::DEFAULT_OPENEMR_BASE_URL_API;
        self::$testClient = new ApiTestClient($baseUrl, false);
        self::$baseUrl = $baseUrl;
        $infernoUrl = getenv("INFERNO_BASE_URL", true) ?: self::DEFAULT_INFERNO_BASE_URL;
        self::$infernoClient = new Client(['timeout' => self::MAX_POLLING_ATTEMPTS, 'base_uri' => $infernoUrl . '/api/']);
        $suiteResponse = self::$infernoClient->get('test_suites/' . self::TEST_SUITE);
        self::$suiteResponse = json_decode($suiteResponse->getBody(), true);
        self::buildIdMap(self::$suiteResponse['test_groups'] ?? []);
        $response = self::$infernoClient->post('test_sessions', [
            'json' => [
                'test_suite_id' => self::TEST_SUITE
            ]
        ]);
        $jsonResponse = json_decode($response->getBody(), true);
        self::$sessionId = $jsonResponse['id'] ?? null;
        if (!self::$sessionId) {
            throw new \Exception("Failed to create test session for Inferno Single Patient API tests");
        }
        self::$previousProfileValue = QueryUtils::fetchSingleValue("SELECT `gl_value` FROM globals WHERE `gl_name` = ?"
            , 'gl_value', [GlobalConnectorsEnum::FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION->value]) ?? '';
        self::setupG10ProfileGlobals();
    }

    protected static function setupG10ProfileGlobals() {
        QueryUtils::sqlStatementThrowException("UPDATE globals SET `gl_value` = ? WHERE `gl_name` = ?"
            , ['3.1.1', GlobalConnectorsEnum::FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION->value]);
        $value = QueryUtils::fetchSingleValue("SELECT `gl_value` FROM globals WHERE `gl_name` = ?"
            , 'gl_value', [GlobalConnectorsEnum::FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION->value]) ?? '';
        if ($value !== '3.1.1') {
            throw new Exception("Failed to set FHIR US Core Max Supported Profile Version to 3.1.1 for G10 Certification tests");
        }
    }

    public static function teardownG10Test() {
        QueryUtils::sqlStatementThrowException("UPDATE globals SET `gl_value` = ? WHERE `gl_name` = ?"
            , [GlobalConnectorsEnum::FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION->value, self::$previousProfileValue]);
    }

    private static function buildIdMap(array $testGroups): void
    {
        foreach ($testGroups as $testGroup) {
            self::$idMap[$testGroup['id']] = ['id' => $testGroup['id'], 'short_id' => $testGroup['short_id'], 'title' => $testGroup['title']];
            if (!empty($testGroup['test_groups'])) {
                self::buildIdMap($testGroup['test_groups']);
            }
            if (!empty($testGroup['tests'])) {
                foreach ($testGroup['tests'] as $test) {
                    self::$idMap[$test['id']] = ['id' => $test['id'], 'short_id' => $test['short_id'], 'title' => $test['title']];
                }
            }
        }
    }
    private static function getDisplayName(string $test_id): string
    {
        if (isset(self::$idMap[$test_id])) {
            return (self::$idMap[$test_id]['short_id'] ?? '') . '.' . self::$idMap[$test_id]['title'] ?? ' Unknown Test';
        }
        return $test_id;
    }



    protected function getTestSuitePrefix()
    {
        if (self::TEST_SUITE === self::TEST_SUITE_US_CORE_V311) {
            return 'us_core_v311-us_core_v311_fhir_api-us_core_v311_';
        } elseif (self::TEST_SUITE === self::TEST_SUITE_G10_CERTIFICATION) {
            return 'g10_certification-';
        } else {
            throw new \Exception("Unknown test suite: " . self::TEST_SUITE);
        }
    }
    protected function renderResults(array $results, string $assertMessage, array $testIdsToSkipFailures = []): void
    {
        foreach ($results as $result) {
            $failMessage = '';
            if (in_array($result['result'], ['skip', 'pass', 'omit'], true)) {
                continue; // skip this test if it's skipped
            }
            if (!empty($result['test_id'])) {
                if (in_array($result['test_id'], $testIdsToSkipFailures)) {
                    continue; // skip this test if it's in the skip list
                }
            } elseif (!empty($result['test_group_id'])) {
                if (in_array($result['test_group_id'], $testIdsToSkipFailures)) {
                    continue; // skip this test if it's in the skip list
                }
            }

            if (!empty($result['result_message'])) {
                $failMessage .= "\n  " . $result['result_message'];
            }
            if (!empty($result['requests'])) {
                foreach ($result['requests'] as $request) {
                    if ($request['result_id'] == $result['id']) {
                        $failMessage .= "\n  Request: " . $request['verb'] . " " . $request['url'] . " status " . $request['status'];
                    }
                }
            }
            if (!empty($result['messages'])) {
                foreach ($result['messages'] as $message) {
                    $failMessage .= "\n  Message(type=" . $message['type'] . "): " . $message['message'];
                }
            }
            // if we wanted to we could also include the inputs and outputs here
            echo self::getDisplayName($result['test_id'] ?? $result['test_group_id']) . ": " . $result['result'] . "\n";
            if (!empty($failMessage)) {
                echo "  ======================================================";
                echo "\n" . "  " . $failMessage . "\n";
                echo "  ******************************************************\n\n";
            }
        }
    }



    protected function getTestGroupResponse(string $testGroupId, array $inputs): array
    {
        $testRunData = [
            'test_session_id' => self::$sessionId,
            'test_group_id' => $testGroupId,
            'inputs' => $inputs
        ];
        $testRunResponse = self::$infernoClient->post('test_runs', [
            'json' => $testRunData
        ]);
        // we grab the response.id JSON value from the result and store it in $testRunId
        $this->assertEquals(200, $testRunResponse->getStatusCode(), "Failed to create test run for " . $testGroupId);
        $testRunJsonResponse = json_decode($testRunResponse->getBody(), true);
        $testRunId = $testRunJsonResponse['id'] ?? null;
        $this->assertNotNull($testRunId, "Test run ID is null");

        // we then need to do a polling request every second to the inferno /test_runs/$testRunId?include_results=false
        // until the status is 'done'
        $maxRetries = self::MAX_POLLING_ATTEMPTS;
        $retryCount = 0;
        $errorCount = 0;
        $maxErrorRetries = 3; // if we get 5 polling errors in a row we can't assume its database contention anymore
        $status = '';
        while ($retryCount < $maxRetries) {
            try {
                $testRunStatusResponse = self::$infernoClient->get("test_runs/{$testRunId}?include_results=false");
                $errorCount = 0; // reset the error count.
                $this->assertEquals(200, $testRunStatusResponse->getStatusCode(), "Failed to get test run status for " . $testGroupId);
                $testRunStatusJson = json_decode($testRunStatusResponse->getBody(), true);
                $status = $testRunStatusJson['status'] ?? '';
                if ($status === 'done') {
                    break;
                }
            } catch (ServerException $exception) {
                (new SystemLogger())->errorLogCaller("Server exception occurred ", [$exception->getMessage()]);
                if ($errorCount++ >= $maxErrorRetries) {
                    throw $exception;
                }
            }
            sleep(self::POLLING_INTERVAL);
            $retryCount++;
        }

        // once the status is 'done', we can make a final request to inferno /test_runs/$testRunId?include_results=true
        // and we can then verify the results against the expected results for the Inferno Single Patient API tests.
        $this->assertEquals('done', $status, "Test run did not complete in time");
        $finalTestRunResponse = self::$infernoClient->get("test_runs/{$testRunId}?include_results=true");
        $this->assertEquals(200, $finalTestRunResponse->getStatusCode(), "Failed to get final test run results for " . $testGroupId);
        $finalTestRunJson = json_decode($finalTestRunResponse->getBody(), true);
        $this->assertNotEmpty($finalTestRunJson['results'], "Test run results are empty for " . $testGroupId);
        return $finalTestRunJson;
    }
}
