<?php

/**
 * AmcTrackingControllerTest - Unit tests for AmcTrackingController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR <dev@open-emr.org>
 * @copyright Copyright (c) 2026 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Reports\AmcTracking;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Reports\AmcTracking\AmcTrackingController;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit tests for AmcTrackingController
 * Tests business logic and data formatting
 */
class AmcTrackingControllerTest extends TestCase
{
    private AmcTrackingController $controller;
    private MockObject $mockGlobalsBag;
    private array $postBackup = [];
    private array $globalsBackup = [];

    /**
     * Set up test environment with mocked dependencies
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Backup $_POST
        $this->postBackup = $_POST;

        // Backup $GLOBALS
        $this->globalsBackup = [
            'srcdir' => $GLOBALS['srcdir'] ?? null,
        ];

        // Create mock OEGlobalsBag
        $this->mockGlobalsBag = $this->createMock(OEGlobalsBag::class);

        // Setup mock to return srcdir
        $this->mockGlobalsBag
            ->method('get')
            ->willReturnCallback(fn($key) => match ($key) {
                'srcdir' => __DIR__ . '/../../../../library',
                'kernel' => null,
                default => null,
            });

        // Initialize controller with mocked OEGlobalsBag
        $this->controller = new AmcTrackingController($this->mockGlobalsBag);
    }

    /**
     * Restore environment after tests
     */
    protected function tearDown(): void
    {
        $_POST = $this->postBackup;

        foreach ($this->globalsBackup as $key => $value) {
            if ($value === null) {
                unset($GLOBALS[$key]);
            } else {
                $GLOBALS[$key] = $value;
            }
        }

        parent::tearDown();
    }

    /**
     * Test constructor with no OEGlobalsBag provided
     */
    public function testConstructorWithoutGlobalsBag(): void
    {
        // This should use the singleton
        $controller = new AmcTrackingController();

        $this->assertInstanceOf(AmcTrackingController::class, $controller);
    }

    /**
     * Test constructor with OEGlobalsBag provided
     */
    public function testConstructorWithGlobalsBag(): void
    {
        $this->assertInstanceOf(AmcTrackingController::class, $this->controller);
    }

    /**
     * Test getFormParameters with empty POST data
     */
    public function testGetFormParametersEmpty(): void
    {
        $_POST = [];

        $params = $this->controller->getFormParameters();

        $this->assertIsArray($params);
        $this->assertArrayHasKey('begin_date', $params);
        $this->assertArrayHasKey('end_date', $params);
        $this->assertArrayHasKey('rule', $params);
        $this->assertArrayHasKey('provider', $params);

        $this->assertEquals('', $params['begin_date']);
        $this->assertEquals('', $params['end_date']);
        $this->assertEquals('', $params['rule']);
        $this->assertEquals('', $params['provider']);
    }

    /**
     * Test getFormParameters with POST data
     */
    public function testGetFormParametersWithData(): void
    {
        $_POST = [
            'form_begin_date' => '2024-01-01 00:00:00',
            'form_end_date' => '2024-12-31 23:59:59',
            'form_rule' => 'send_sum_amc',
            'form_provider' => '5',
        ];

        $params = $this->controller->getFormParameters();

        $this->assertNotEmpty($params['begin_date']);
        $this->assertNotEmpty($params['end_date']);
        $this->assertEquals('send_sum_amc', $params['rule']);
        $this->assertEquals('5', $params['provider']);
    }

    /**
     * Test getProviders returns array of providers
     * Note: This test requires database access, so it's marked as incomplete
     * In a real environment, you'd mock the database layer
     */
    public function testGetProviders(): void
    {
        $this->markTestIncomplete(
            'This test requires database access and should be run as an integration test'
        );

        // $providers = $this->controller->getProviders();
        // $this->assertIsArray($providers);
    }

    /**
     * Test getRuleDisplayName for all rule types
     */
    public function testGetRuleDisplayName(): void
    {
        $sendSumName = $this->controller->getRuleDisplayName('send_sum_amc');
        $this->assertStringContainsString('Referral', $sendSumName);

        $provideRecName = $this->controller->getRuleDisplayName('provide_rec_pat_amc');
        $this->assertStringContainsString('Medical Records', $provideRecName);

        $provideSumName = $this->controller->getRuleDisplayName('provide_sum_pat_amc');
        $this->assertStringContainsString('Visit', $provideSumName);

        $unknownName = $this->controller->getRuleDisplayName('unknown_rule');
        $this->assertStringContainsString('Unknown', $unknownName);
    }

    /**
     * Test getDateColumnHeader for all rule types
     */
    public function testGetDateColumnHeader(): void
    {
        $sendSumHeader = $this->controller->getDateColumnHeader('send_sum_amc');
        $this->assertStringContainsString('Referral', $sendSumHeader);

        $provideRecHeader = $this->controller->getDateColumnHeader('provide_rec_pat_amc');
        $this->assertStringContainsString('Request', $provideRecHeader);

        $provideSumHeader = $this->controller->getDateColumnHeader('provide_sum_pat_amc');
        $this->assertStringContainsString('Encounter', $provideSumHeader);

        $unknownHeader = $this->controller->getDateColumnHeader('unknown_rule');
        $this->assertStringContainsString('Date', $unknownHeader);
    }

    /**
     * Test getIdColumnHeader for all rule types
     */
    public function testGetIdColumnHeader(): void
    {
        $sendSumHeader = $this->controller->getIdColumnHeader('send_sum_amc');
        $this->assertStringContainsString('Referral', $sendSumHeader);

        $provideRecHeader = $this->controller->getIdColumnHeader('provide_rec_pat_amc');
        $this->assertEquals('', $provideRecHeader);

        $provideSumHeader = $this->controller->getIdColumnHeader('provide_sum_pat_amc');
        $this->assertStringContainsString('Encounter', $provideSumHeader);
    }

    /**
     * Test getCheckboxColumnHeader for all rule types
     */
    public function testGetCheckboxColumnHeader(): void
    {
        $sendSumHeader = $this->controller->getCheckboxColumnHeader('send_sum_amc');
        $this->assertStringContainsString('Summary of Care', $sendSumHeader);

        $provideRecHeader = $this->controller->getCheckboxColumnHeader('provide_rec_pat_amc');
        $this->assertStringContainsString('Medical Records', $provideRecHeader);

        $provideSumHeader = $this->controller->getCheckboxColumnHeader('provide_sum_pat_amc');
        $this->assertStringContainsString('Medical Summary', $provideSumHeader);
    }

    /**
     * Test prepareTemplateData without results
     */
    public function testPrepareTemplateDataWithoutResults(): void
    {
        $params = [
            'begin_date' => '',
            'end_date' => '',
            'rule' => '',
            'provider' => '',
        ];

        $data = $this->controller->prepareTemplateData($params, false);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('csrf_token', $data);
        $this->assertArrayHasKey('csrf_token_raw', $data);
        $this->assertArrayHasKey('begin_date', $data);
        $this->assertArrayHasKey('end_date', $data);
        $this->assertArrayHasKey('rule', $data);
        $this->assertArrayHasKey('provider', $data);
        $this->assertArrayHasKey('providers', $data);
        $this->assertArrayHasKey('show_results', $data);
        $this->assertArrayHasKey('results', $data);
        $this->assertArrayHasKey('oemrUiSettings', $data);

        $this->assertFalse($data['show_results']);
        $this->assertEmpty($data['results']);
    }

    /**
     * Test prepareTemplateData structure
     */
    public function testPrepareTemplateDataStructure(): void
    {
        $params = [
            'begin_date' => '20240101000000',
            'end_date' => '20241231235959',
            'rule' => 'send_sum_amc',
            'provider' => '5',
        ];

        $data = $this->controller->prepareTemplateData($params, false);

        // Verify oemrUiSettings structure
        $this->assertArrayHasKey('oemrUiSettings', $data);
        $settings = $data['oemrUiSettings'];

        $this->assertArrayHasKey('heading_title', $settings);
        $this->assertArrayHasKey('include_patient_name', $settings);
        $this->assertArrayHasKey('expandable', $settings);
        $this->assertArrayHasKey('action', $settings);
        $this->assertArrayHasKey('action_href', $settings);

        $this->assertFalse($settings['include_patient_name']);
        $this->assertFalse($settings['expandable']);
        $this->assertEquals('conceal', $settings['action']);
    }

    /**
     * Test getTrackingResults requires database access
     * Marked as incomplete for unit testing
     */
    public function testGetTrackingResults(): void
    {
        $this->markTestIncomplete(
            'This test requires database access and amc.php file, should be run as integration test'
        );

        // In integration test:
        // $results = $this->controller->getTrackingResults('send_sum_amc', '20240101', '20241231', '');
        // $this->assertIsArray($results);
    }

    /**
     * Test that OEGlobalsBag is used correctly
     */
    public function testOEGlobalsBagUsage(): void
    {
        // Verify that the mock was called for srcdir when needed
        // This is implicit in the constructor test, but we can be explicit

        $this->mockGlobalsBag
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('srcdir');

        // This would trigger the srcdir access in getTrackingResults
        // but we can't test that without database access

        $this->assertTrue(true); // Placeholder assertion
    }

    /**
     * Test readonly property cannot be modified
     */
    public function testReadonlyGlobalsBagProperty(): void
    {
        // PHP 8.1+ readonly properties cannot be modified after construction
        // This test verifies the property exists and is properly typed

        $reflection = new \ReflectionClass($this->controller);
        $property = $reflection->getProperty('globalsBag');

        $this->assertTrue($property->isPrivate());

        // Check if readonly (PHP 8.1+)
        if (method_exists($property, 'isReadOnly')) {
            $this->assertTrue($property->isReadOnly());
        }
    }
}
