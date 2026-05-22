<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Certification\HIT1\US_Core_311;

use GuzzleHttp\Client;
use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\TestCase;

/**
 * These are best-effort representations of the Inferno output based on
 * trawling through the logs. I wasn't able to find concrete documentation on
 * the shape, and this is an approximation regardless (the results are more of
 * a sum type that can't be easily expressed to PHPStan)
 *
 * @phpstan-type Message array{
 *   message: string,
 *   type: string,
 * }
 *
 * @phpstan-type Input array{
 *   name: string,
 *   label: string,
 *   description: string,
 *   value: string,
 *   type: string,
 * }
 *
 * @phpstan-type Output array{
 *   name: string,
 *   type: string,
 *   value: string,
 * }
 *
 * @phpstan-type Request array{
 *   id: string,
 *   direction: string,
 *   index: int,
 *   result_id: string,
 *   status: int,
 *   timestamp: string,
 *   url: string,
 *   verb: string,
 * }
 *
 * @phpstan-type TestResult array{
 *   id: string,
 *   created_at: string,
 *   inputs: Input[],
 *   messages?: Message[],
 *   optional: bool,
 *   outputs: Output[],
 *   requests?: Request[],
 *   result: 'pass'|'fail'|'skip'|'omit'|'error',
 *   result_message?: string,
 *   test_id?: string,
 *   test_group_id?: string,
 *   test_run_id: string,
 *   test_session_id: string,
 *   updated_at: string,
 * }
 *
 * @phpstan-type TestInput array{name: string, value: string|array<string, string>}
 */
final class InfernoSinglePatientAPITest extends TestCase
{
    // Alice Jones (96506861-511f-4f6d-bc97-b65a78cf1995),
    // Jeremy Bates (96891ab2-01ad-49f9-9958-cdad71bd33c1),
    // Happy Child (968944d0-180a-48ac-8049-636ae8ac6127),
    // and John Appleseed (969f72c3-0256-488e-b25b-8fff3af18b1b)
    // are the patients used in the Inferno Single Patient API tests.
    public const PATIENT_IDS = '96506861-511f-4f6d-bc97-b65a78cf1995,96891ab2-01ad-49f9-9958-cdad71bd33c1,968944d0-180a-48ac-8049-636ae8ac6127';
    public const PATIENT_ID_PRIMARY = '96506861-511f-4f6d-bc97-b65a78cf1995';

    // Held separately due to a quirk in how Inferno processes the
    // additional patient list in the Single Patient API tests.
    public const ADDITIONAL_PATIENT_IDS = '96891ab2-01ad-49f9-9958-cdad71bd33c1,968944d0-180a-48ac-8049-636ae8ac6127';

    public const TEST_SUITE_US_CORE_V311 = 'us_core_v311';
    public const TEST_SUITE_G10_CERTIFICATION = 'g10_certification';

    public const DEFAULT_TEST_SUITE = self::TEST_SUITE_US_CORE_V311;
    public const DEFAULT_OPENEMR_BASE_URL_API = 'http://openemr';
    public const DEFAULT_INFERNO_BASE_URL = 'http://nginx';
    public const DEFAULT_TEST_GROUP_ID = 'us_core_v311-us_core_v311_fhir_api';
    public const TIMEOUT = 60; // seconds

    private static ApiTestClient $testClient;
    private static string $baseUrl;
    private static Client $infernoClient;
    private static ?string $sessionId = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: self::DEFAULT_OPENEMR_BASE_URL_API;
        self::$testClient = new ApiTestClient($baseUrl, false);
        self::$baseUrl = $baseUrl;
        // For now this uses the admin user to authenticate.
        // TODO: @adunsulag implement this using a test practitioner user so we can
        // test the inferno single patient API from a regular provider.
        self::$testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $accessToken = self::$testClient->getAccessToken();
        if (!is_string($accessToken) || $accessToken === '') {
            throw new \RuntimeException('Failed to obtain access token for Inferno Single Patient API tests');
        }
        $infernoUrl = getenv('INFERNO_BASE_URL', true) ?: self::DEFAULT_INFERNO_BASE_URL;
        self::$infernoClient = new Client(['timeout' => self::TIMEOUT, 'base_uri' => $infernoUrl . '/api/']);
        $response = self::$infernoClient->post('test_sessions?test_suite_id=' . self::currentSuite());
        $jsonResponse = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($jsonResponse, 'Test session response must be a JSON object');
        $sessionId = $jsonResponse['id'] ?? null;
        if (!is_string($sessionId) || $sessionId === '') {
            throw new \RuntimeException('Failed to create test session for Inferno Single Patient API tests');
        }
        self::$sessionId = $sessionId;
    }

    public function testCapabilityStatement(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'capability_statement');
        $this->assertResultsPassed(
            $response['results'],
            'Capability Statement test failed',
            [
                // Skip the standalone auth TLS test for now as the unit test environment does not support TLS.
                'us_core_v311-us_core_v311_fhir_api-us_core_v311_capability_statement-standalone_auth_tls',
                // Skip the overall group failure test as the sub test failing triggers the group failure.
                'us_core_v311-us_core_v311_fhir_api-us_core_v311_capability_statement',
            ]
        );
    }

    public function testPatient(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'patient', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Patient Resource test failed');
    }

    public function testAllergyIntolerance(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'allergy_intolerance', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'AllergyIntolerance Resource test failed');
    }

    public function testCarePlan(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'care_plan', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'CarePlan Resource test failed');
    }

    public function testCondition(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'condition', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Condition Resource test failed');
    }

    public function testDevice(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'device', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Device Resource test failed');
    }

    public function testDiagnosticReportNote(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'diagnostic_report_note', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'DiagnosticReport and Note exchange test failed');
    }

    public function testDiagnosticReportLab(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'diagnostic_report_lab', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'DiagnosticReport Laboratory Resource test failed');
    }

    public function testDocumentReference(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'document_reference', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Document Reference Resource test failed');
    }

    public function testGoal(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'goal', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Goal Resource test failed');
    }

    public function testImmunization(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'immunization', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Immunization Resource test failed');
    }

    public function testMedicationRequest(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'medication_request', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'MedicationRequest Resource test failed');
    }

    public function testSmokingStatus(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'smokingstatus', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Smoking Status Observation Resource test failed');
    }

    public function testPediatricWeightForHeight(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'pediatric_weight_for_height', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Pediatric Weight for Height Observation Resource test failed');
    }

    public function testObservationLab(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'observation_lab', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Observation Laboratory Resource test failed');
    }

    public function testPediatricBmiForAge(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'pediatric_bmi_for_age', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Pediatric BMI for Age Observation Resource test failed');
    }

    public function testPulseOximetry(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'pulse_oximetry', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Pulse Oximetry Observation Resource test failed');
    }

    public function testHeadCircumference(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'head_circumference', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Head Circumference Observation Resource test failed');
    }

    public function testBodyHeight(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'bodyheight', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Body Height Observation Resource test failed');
    }

    public function testBodyTemp(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'bodytemp', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Body Temperature Observation Resource test failed');
    }

    public function testBloodPressure(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'bp', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Blood Pressure Observation Resource test failed');
    }

    public function testBodyWeight(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'bodyweight', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Body Weight Observation Resource test failed');
    }

    public function testHeartRate(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'heartrate', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Heart rate Observation Resource test failed');
    }

    public function testRespRate(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'resprate', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Respiratory Rate Observation Resource test failed');
    }

    public function testProcedure(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'procedure', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Procedure Resource test failed');
    }

    public function testEncounter(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'encounter', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Encounter Resource test failed');
    }

    public function testOrganization(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'organization', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Organization Resource test failed');
    }

    public function testPractitioner(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'practitioner', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Practitioner Resource test failed');
    }

    public function testClinicalNotesGuidance(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'clinical_notes_guidance', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Clinical Notes Guidance test failed');
    }

    public function testDataAbsentReason(): void
    {
        // data_absent_reason is not a standalone runnable in US Core 3.1.1 suite.
        // It's tested as part of the G10 certification suite (test 4.32).
        if (self::currentSuite() !== self::TEST_SUITE_G10_CERTIFICATION) {
            $this->markTestSkipped('data_absent_reason test group is only available in G10 certification suite');
        }
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'data_absent_reason', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Data Absent Reason test failed');
    }

    // Provenance runs last because it is the most complex test and can take a while.
    public function testProvenance(): void
    {
        $response = $this->getTestGroupResponse($this->getTestSuitePrefix() . 'provenance', 'smart_auth_info');
        $this->assertResultsPassed($response['results'], 'Us Core Provenance Resource test failed');
    }

    /**
     * Resolved at runtime from the INFERNO_TEST_SUITE env var (falling back to
     * DEFAULT_TEST_SUITE) so PHPStan cannot collapse suite comparisons at
     * compile time and so the suite can be switched without editing the file.
     */
    protected static function currentSuite(): string
    {
        return getenv('INFERNO_TEST_SUITE', true) ?: self::DEFAULT_TEST_SUITE;
    }

    protected function getTestSuitePrefix(): string
    {
        $suite = self::currentSuite();
        if ($suite === self::TEST_SUITE_US_CORE_V311) {
            return 'us_core_v311-us_core_v311_fhir_api-us_core_v311_';
        }
        if ($suite === self::TEST_SUITE_G10_CERTIFICATION) {
            return 'g10_certification-g10_single_patient_api-us_core_v311_';
        }
        throw new \RuntimeException('Unknown test suite: ' . $suite);
    }

    /**
     * @param TestResult[] $results
     * @param list<string> $testIdsToSkipFailures
     */
    protected function assertResultsPassed(array $results, string $assertMessage, array $testIdsToSkipFailures = []): void
    {
        foreach ($results as $result) {
            $failMessage = '';
            if (in_array($result['result'], ['skip', 'omit'], true)) {
                continue;
            }
            $testId = $result['test_id'] ?? '';
            $testGroupId = $result['test_group_id'] ?? '';
            if (in_array($testId, $testIdsToSkipFailures, true) || in_array($testGroupId, $testIdsToSkipFailures, true)) {
                continue;
            }
            if ($result['result'] !== 'pass') {
                if ($testId !== '') {
                    $this->assertArrayHasKey('result_message', $result, 'Message should be present for non-pass results');
                    $failMessage = $assertMessage . ' for test ' . $testId . ' ' . $result['result_message'];
                } elseif ($testGroupId !== '') {
                    $failMessage = $assertMessage . ' for test group ' . $testGroupId;
                } else {
                    $this->assertArrayHasKey('result_message', $result, 'Message should be present for non-pass results');
                    $failMessage = $assertMessage . ' ' . $result['result_message'];
                }
                foreach ($result['requests'] ?? [] as $request) {
                    if ($request['result_id'] === $result['id']) {
                        $failMessage .= "\nRequest: " . $request['verb'] . ' ' . $request['url'] . ' status ' . $request['status'];
                    }
                }
                foreach ($result['messages'] ?? [] as $message) {
                    $failMessage .= "\nMessage(type=" . $message['type'] . '): ' . $message['message'];
                }
            }
            $this->assertSame('pass', $result['result'], $failMessage);
        }
    }

    /**
     * @return list<TestInput>
     */
    protected function getTestInputs(string $credentialsKeyName, string $accessToken): array
    {
        if (self::currentSuite() === self::TEST_SUITE_G10_CERTIFICATION) {
            return [
                ['name' => 'url', 'value' => self::$baseUrl . '/apis/default/fhir'],
                ['name' => 'patient_id', 'value' => self::PATIENT_ID_PRIMARY],
                ['name' => 'patient_ids', 'value' => self::PATIENT_IDS],
                ['name' => 'additional_patient_ids', 'value' => self::ADDITIONAL_PATIENT_IDS],
                ['name' => $credentialsKeyName, 'value' => ['access_token' => $accessToken]],
            ];
        }
        return [
            ['name' => 'url', 'value' => self::$baseUrl . '/apis/default/fhir'],
            ['name' => 'patient_ids', 'value' => self::PATIENT_IDS],
            ['name' => $credentialsKeyName, 'value' => ['access_token' => $accessToken]],
        ];
    }

    /**
     * @return array{results: TestResult[]}
     */
    protected function getTestGroupResponse(string $testGroupId, string $credentialsKeyName = 'smart_credentials'): array
    {
        $accessToken = self::$testClient->getAccessToken();
        $this->assertNotNull($accessToken, 'Access token must be set before running test groups');
        $testRunData = [
            'test_session_id' => self::$sessionId,
            'test_group_id' => $testGroupId,
            'inputs' => $this->getTestInputs($credentialsKeyName, $accessToken),
        ];
        $testRunResponse = self::$infernoClient->post('test_runs', ['json' => $testRunData]);
        $this->assertSame(200, $testRunResponse->getStatusCode(), 'Failed to create test run for ' . $testGroupId);
        $testRunJsonResponse = json_decode((string) $testRunResponse->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($testRunJsonResponse, 'Test run response must be a JSON object');
        $testRunId = $testRunJsonResponse['id'] ?? null;
        $this->assertIsString($testRunId, 'Test run ID must be a string');
        $this->assertNotSame('', $testRunId, 'Test run ID must be a non-empty string');

        // Poll /test_runs/$testRunId?include_results=false every 500 ms until the
        // status is 'done'; if it never reaches 'done' before the timeout, fail.
        $maxRetries = self::TIMEOUT * 2;
        $retryCount = 0;
        $status = '';
        while ($retryCount < $maxRetries) {
            $testRunStatusResponse = self::$infernoClient->get("test_runs/{$testRunId}?include_results=false");
            $this->assertSame(200, $testRunStatusResponse->getStatusCode(), 'Failed to get test run status for ' . $testGroupId);
            $testRunStatusJson = json_decode((string) $testRunStatusResponse->getBody(), true, flags: JSON_THROW_ON_ERROR);
            $this->assertIsArray($testRunStatusJson, 'Test run status response must be a JSON object');
            $status = $testRunStatusJson['status'] ?? '';
            if ($status === 'done') {
                break;
            }
            usleep(500000);
            $retryCount++;
        }

        // Once status is 'done', request the final run with include_results=true
        // and verify the results.
        $this->assertSame('done', $status, 'Test run did not complete in time');
        $finalTestRunResponse = self::$infernoClient->get("test_runs/{$testRunId}?include_results=true");
        $this->assertSame(200, $finalTestRunResponse->getStatusCode(), 'Failed to get final test run results for ' . $testGroupId);
        $finalTestRunJson = json_decode((string) $finalTestRunResponse->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($finalTestRunJson, 'Final test run response must be a JSON object');
        $this->assertArrayHasKey('results', $finalTestRunJson, 'Final test run response missing results for ' . $testGroupId);
        $this->assertIsArray($finalTestRunJson['results'], 'Final test run results must be an array for ' . $testGroupId);
        $this->assertNotEmpty($finalTestRunJson['results'], 'Test run results are empty for ' . $testGroupId);
        /** @var array{results: TestResult[]} $finalTestRunJson */
        return $finalTestRunJson;
    }
}
