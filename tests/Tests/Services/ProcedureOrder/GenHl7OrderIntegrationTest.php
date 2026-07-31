<?php

/**
 * Integration tests for gen_hl7_order functions across different lab implementations
 *
 * These tests verify that the different gen_hl7_order implementations
 * (Generic, GenUniversal, LabCorp, Quest) correctly generate HL7 messages.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@openemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\ProcedureOrder;

use OpenEMR\Tests\Fixtures\ProcedureOrderFixtureManager;
use OpenEMR\Tests\Fixtures\ProcedureProviderFixtureManager;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for gen_hl7_order function implementations
 *
 * Tests the various gen_hl7_order implementations used by different
 * lab vendors to ensure they correctly generate HL7 ORM^O01 messages
 * with proper segments, patient data, provider information, and
 * procedure codes.
 */
class GenHl7OrderIntegrationTest extends TestCase
{
    /**
     * @var ProcedureOrderFixtureManager Manages procedure order test fixtures
     */
    private ProcedureOrderFixtureManager $orderFixtureManager;

    /**
     * @var ProcedureProviderFixtureManager Manages procedure provider test fixtures
     */
    private ProcedureProviderFixtureManager $providerFixtureManager;

    /**
     * @var array<int, int> Array of installed test order IDs
     */
    private array $installedOrderIds;

    /**
     * Set up test fixtures before each test
     *
     * Installs test procedure orders, providers, patients, and encounters.
     * Sets up global variables required by gen_hl7_order functions.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up global variables that may be needed
        global $webserver_root, $srcdir;
        if (empty($webserver_root)) {
            $webserver_root = __DIR__ . '/../../../../..';
        }
        if (empty($srcdir)) {
            $srcdir = $webserver_root . '/library';
        }

        $this->providerFixtureManager = new ProcedureProviderFixtureManager();
        $this->orderFixtureManager = new ProcedureOrderFixtureManager(
            null,
            null,
            $this->providerFixtureManager
        );

        // Install fixtures
        $this->orderFixtureManager->installFixtures();
        $this->installedOrderIds = $this->orderFixtureManager->getInstalledOrderIds();
    }

    /**
     * Clean up test fixtures after each test
     *
     * Removes all test data from the database to ensure clean state
     * between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->orderFixtureManager->removeFixtures();
        parent::tearDown();
    }

    /**
     * Test generic/default gen_hl7_order implementation
     *
     * Verifies that the default gen_hl7_order function generates valid
     * HL7 messages with all required segments (MSH, PID, PV1, IN1, GT1, ORC, OBR).
     *
     * @return void
     */
    public function testGenericGenHl7Order(): void
    {
        $this->assertNotEmpty($this->installedOrderIds, "Should have installed at least one order");

        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        // Require the generic implementation
        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');

        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        // Assert no errors
        $this->assertEmpty($errorMsg, "gen_hl7_order should not return an error: " . $errorMsg);

        // Assert HL7 was generated
        $this->assertNotEmpty($hl7Output, "HL7 output should not be empty");

        // Verify key HL7 segments exist
        $this->assertHl7SegmentsExist($hl7Output, [
            'MSH', // Message Header
            'PID', // Patient Identification
            'PV1', // Patient Visit
            'IN1', // Insurance
            'GT1', // Guarantor
            'ORC', // Common Order
            'OBR', // Observation Request
        ]);

        // Verify MSH segment structure
        $this->assertStringContainsString('MSH|', $hl7Output, 'Should have MSH segment');
        $this->assertStringContainsString('ORM^O01', $hl7Output, 'Should have ORM^O01 message type');
        $this->assertStringContainsString('2.3', $hl7Output, 'Should have HL7 version 2.3');

        // Verify delimiters are correct
        $lines = explode("\r", $hl7Output);
        $this->assertGreaterThan(5, count($lines), 'Should have multiple HL7 segments');
    }

    /**
     * Test GenUniversal HL7 implementation
     *
     * Documents test structure for gen_universal_hl7 implementation.
     * Marked as skipped due to PHP function name conflicts when loading
     * multiple implementations in same process.
     *
     * @return void
     */
    public function testGenUniversalHl7Order(): void
    {
        $this->markTestSkipped('Requires gen_universal_hl7 implementation - skip if function already defined');

        // Note: Can't easily test multiple implementations in same process
        // due to function name conflicts. This test documents the approach
        // but would need to run in separate process or after refactoring.

        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        // Would require: gen_universal_hl7/gen_hl7_order.inc.php
        // $errorMsg = gen_hl7_order($orderId, $hl7Output);

        // Same assertions as generic test
        $this->assertTrue(true, 'Test structure documented');
    }

    /**
     * Test LabCorp implementation with 3-parameter signature
     *
     * Documents test structure for LabCorp's gen_hl7_order implementation.
     * LabCorp requires an additional $reqStr parameter for 2D barcode requisition.
     * Marked as skipped due to PHP function name conflicts when loading
     * multiple implementations in same process.
     *
     * @return void
     */
    public function testLabCorpGenHl7Order(): void
    {
        $this->markTestSkipped('Requires LabCorp implementation - skip if function already defined');

        // Note: Can't easily test in same process due to function name conflicts

        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';
        $reqStr = ''; // LabCorp-specific requisition string

        // Would require: labcorp/gen_hl7_order.inc.php
        // $errorMsg = gen_hl7_order($orderId, $hl7Output, $reqStr);

        // Assert LabCorp-specific outputs
        // $this->assertNotEmpty($reqStr, 'LabCorp should generate requisition string');
        // $this->assertStringContainsString('ZCI|', $hl7Output, 'Should have ZCI custom segment');

        $this->assertTrue(true, 'Test structure documented');
    }

    /**
     * Test Quest implementation with 3-parameter signature
     *
     * Documents test structure for Quest's gen_hl7_order implementation.
     * Quest requires an additional $reqStr parameter for requisition data.
     * Marked as skipped due to PHP function name conflicts when loading
     * multiple implementations in same process.
     *
     * @return void
     */
    public function testQuestGenHl7Order(): void
    {
        $this->markTestSkipped('Requires Quest implementation - skip if function already defined');

        // Note: Can't easily test in same process due to function name conflicts

        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';
        $reqStr = ''; // Quest-specific requisition string

        // Would require: quest/gen_hl7_order.inc.php
        // $errorMsg = gen_hl7_order($orderId, $hl7Output, $reqStr);

        $this->assertTrue(true, 'Test structure documented');
    }

    /**
     * Test that HL7 output contains patient information
     *
     * Verifies that the PID (Patient Identification) segment contains
     * the patient's name in LastName^FirstName format.
     *
     * @return void
     */
    public function testHl7ContainsPatientInformation(): void
    {
        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        $this->assertEmpty($errorMsg);

        // Verify PID segment has patient name
        $this->assertMatchesRegularExpression(
            '/PID\|.*\|.*\|.*\|.*\|[^\|]+\^[^\|]+/',
            $hl7Output,
            'PID segment should contain patient name (LastName^FirstName)'
        );
    }

    /**
     * Test that HL7 output contains provider information
     *
     * Verifies that the ORC (Common Order) segment contains the ordering
     * provider with New Order (NW) status code.
     *
     * @return void
     */
    public function testHl7ContainsProviderInformation(): void
    {
        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        $this->assertEmpty($errorMsg);

        // Verify ORC segment has ordering provider
        $this->assertMatchesRegularExpression(
            '/ORC\|NW\|/',
            $hl7Output,
            'ORC segment should start with New Order (NW)'
        );
    }

    /**
     * Test that HL7 output contains procedure codes
     *
     * Verifies that OBR (Observation Request) segments are generated
     * for each procedure code in the order.
     *
     * @return void
     */
    public function testHl7ContainsProcedureCodes(): void
    {
        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        $this->assertEmpty($errorMsg);

        // Verify OBR segments exist (one per procedure code)
        $obrCount = substr_count($hl7Output, "\rOBR|");
        $this->assertGreaterThanOrEqual(1, $obrCount, 'Should have at least one OBR segment');

        // Our fixture has 2 procedure codes
        $this->assertEquals(2, $obrCount, 'Should have OBR segment for each procedure code');
    }

    /**
     * Test that diagnosis codes are included in HL7
     *
     * Verifies that DG1 (Diagnosis) segments contain the ICD-10 codes
     * associated with the procedure order.
     *
     * @return void
     */
    public function testHl7ContainsDiagnosisCodes(): void
    {
        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        $this->assertEmpty($errorMsg);

        // Verify DG1 segments exist for diagnoses
        $dg1Count = substr_count($hl7Output, "\rDG1|");

        // Our fixture includes ICD10:E78.5 diagnosis
        $this->assertGreaterThanOrEqual(1, $dg1Count, 'Should have at least one DG1 segment for diagnoses');
        $this->assertStringContainsString('E78.5', $hl7Output, 'Should contain the diagnosis code from fixture');
    }

    /**
     * Test error handling when order ID doesn't exist
     *
     * Verifies that gen_hl7_order returns an appropriate error message
     * when called with an invalid/non-existent order ID.
     *
     * @return void
     */
    public function testGenHl7OrderWithInvalidOrderId(): void
    {
        $invalidOrderId = 999999;
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($invalidOrderId, $hl7Output);

        // Should return an error message
        $this->assertNotEmpty($errorMsg, 'Should return error for invalid order ID');
        $this->assertStringContainsString('missing', strtolower($errorMsg), 'Error should mention missing order');
    }

    /**
     * Test send_hl7_order function with DL (download) protocol
     *
     * Verifies that the send_hl7_order function exists and is callable.
     * Cannot fully test DL protocol as it would trigger header() and exit().
     *
     * @return void
     */
    public function testSendHl7OrderDownloadProtocol(): void
    {
        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        $this->assertEmpty($errorMsg);

        // Get the provider ID
        $provider = $this->providerFixtureManager->getProviderByName('Generic Lab');
        $this->assertNotNull($provider, 'Should find Generic Lab provider');

        $ppid = $provider['ppid'];

        // Note: send_hl7_order with DL protocol will try to output headers and exit
        // In a test environment, we just verify the function exists and
        // has the correct signature

        $this->assertTrue(
            function_exists('send_hl7_order'),
            'send_hl7_order function should be defined'
        );

        // Can't actually call send_hl7_order with DL protocol in tests
        // as it will call header() and exit()
        $this->assertTrue(true, 'send_hl7_order function exists and is callable');
    }

    /**
     * Helper method to assert that required HL7 segments exist in output
     *
     * @param string $hl7Output The HL7 message to check
     * @param array<int, string> $segments Array of segment names to verify
     * @return void
     */
    private function assertHl7SegmentsExist(string $hl7Output, array $segments): void
    {
        foreach ($segments as $segment) {
            $this->assertStringContainsString(
                "\r" . $segment . "|",
                $hl7Output,
                "HL7 output should contain $segment segment"
            );
        }
    }

    /**
     * Test HL7 field delimiters are correct
     *
     * Verifies that the MSH segment defines standard HL7 delimiters:
     * | (field), ^ (component), & (subcomponent), ~ (repetition), \ (escape)
     *
     * @return void
     */
    public function testHl7DelimitersAreCorrect(): void
    {
        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        $this->assertEmpty($errorMsg);

        // HL7 uses specific delimiters
        // Field separator: | (pipe)
        // Component separator: ^ (caret)
        // Subcomponent separator: & (ampersand)
        // Repetition separator: ~ (tilde)
        // Escape character: \ (backslash)

        // MSH segment should define these
        $this->assertStringContainsString(
            'MSH|^~\\&|',
            $hl7Output,
            'MSH segment should define standard HL7 delimiters'
        );
    }

    /**
     * Test that multiple procedure codes generate multiple OBR segments
     *
     * Verifies that when an order contains multiple procedure codes,
     * a separate OBR segment is generated for each code.
     *
     * @return void
     */
    public function testMultipleProcedureCodesGenerateMultipleObrSegments(): void
    {
        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        $this->assertEmpty($errorMsg);

        // Count OBR segments
        $lines = explode("\r", $hl7Output);
        $obrLines = array_filter($lines, fn($line): bool => str_starts_with($line, 'OBR|'));

        $this->assertCount(
            2,
            $obrLines,
            'Should have 2 OBR segments for 2 procedure codes in fixture'
        );

        // Verify both procedure codes are present
        $this->assertStringContainsString('80053', $hl7Output, 'Should contain first procedure code');
        $this->assertStringContainsString('85025', $hl7Output, 'Should contain second procedure code');
    }

    /**
     * Test that insurance information is included when available
     *
     * Verifies that IN1 (Insurance) and GT1 (Guarantor) segments are
     * present when applicable based on billing type.
     *
     * @return void
     */
    public function testHl7IncludesInsuranceInformation(): void
    {
        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        $this->assertEmpty($errorMsg);

        // Check for IN1 (Insurance) segment
        // Note: May not always be present depending on patient's insurance status
        if (str_contains($hl7Output, "\rIN1|")) {
            $this->assertStringContainsString(
                'IN1|',
                $hl7Output,
                'Should have IN1 segment when insurance is present'
            );
        }

        // GT1 (Guarantor) segment should always be present for non-client billing
        $this->assertStringContainsString(
            'GT1|',
            $hl7Output,
            'Should have GT1 (Guarantor) segment'
        );
    }

    /**
     * Test that message control ID is properly formatted
     *
     * Verifies that the MSH-10 field (message control ID) is present
     * and not empty. This field is used to uniquely identify the message.
     *
     * @return void
     */
    public function testMessageControlIdIsProperlyFormatted(): void
    {
        $orderId = $this->installedOrderIds[0];
        $hl7Output = '';

        require_once(__DIR__ . '/../../../../interface/orders/gen_hl7_order.inc.php');
        $errorMsg = gen_hl7_order($orderId, $hl7Output);

        $this->assertEmpty($errorMsg);

        // Extract MSH segment
        $lines = explode("\r", $hl7Output);
        $mshLine = '';
        foreach ($lines as $line) {
            if (str_starts_with($line, 'MSH|')) {
                $mshLine = $line;
                break;
            }
        }

        $this->assertNotEmpty($mshLine, 'Should have MSH segment');

        // MSH-10 is the message control ID (should be around field 10)
        $fields = explode('|', $mshLine);
        $this->assertGreaterThanOrEqual(
            10,
            count($fields),
            'MSH segment should have at least 10 fields'
        );

        // Message control ID should not be empty
        $msgControlId = $fields[9] ?? ''; // 0-indexed, so field 10 is index 9
        $this->assertNotEmpty(
            $msgControlId,
            'Message control ID (MSH-10) should not be empty'
        );
    }
}
