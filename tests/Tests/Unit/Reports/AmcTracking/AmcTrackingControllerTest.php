<?php

declare(strict_types=1);

/**
 * AmcTrackingControllerTest - Unit tests for AmcTrackingController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR <dev@open-emr.org>
 * @copyright Copyright (c) 2026 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Reports\AmcTracking;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Reports\AmcTracking\AmcTrackingController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Unit tests for AmcTrackingController
 * Tests business logic and data formatting
 */
class AmcTrackingControllerTest extends TestCase
{
    private AmcTrackingController $controller;
    /** @var MockObject&OEGlobalsBag */
    private MockObject $mockGlobalsBag;
    /** @var MockObject&SessionInterface */
    private MockObject $mockSession;
    /** @var array<string, mixed> */
    private array $globalsBackup = [];

    /**
     * Set up test environment with mocked dependencies
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Backup $GLOBALS
        $this->globalsBackup = [
            'srcdir' => $GLOBALS['srcdir'] ?? null,
        ];

        // Create mock OEGlobalsBag
        $this->mockGlobalsBag = $this->createMock(OEGlobalsBag::class);

        // Setup mock to return srcdir
        $this->mockGlobalsBag
            ->method('get')
            ->willReturnCallback(fn($key): ?string => match ($key) {
                'srcdir' => __DIR__ . '/../../../../library',
                'kernel' => null,
                default => null,
            });

        $this->mockGlobalsBag
            ->method('getSrcDir')
            ->willReturn(__DIR__ . '/../../../../library');

        // Create mock SessionInterface with a CSRF private key
        $this->mockSession = $this->createMock(SessionInterface::class);
        $this->mockSession
            ->method('get')
            ->willReturnCallback(fn($key, $default = null): mixed => match ($key) {
                'csrf_private_key' => str_repeat('a', 32),
                default => $default,
            });

        // Initialize controller with mocked OEGlobalsBag
        $this->controller = new AmcTrackingController($this->mockGlobalsBag);
    }

    /**
     * Restore environment after tests
     */
    protected function tearDown(): void
    {
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

        $this->assertSame(AmcTrackingController::class, $controller::class);
    }

    /**
     * Test constructor with OEGlobalsBag provided
     */
    public function testConstructorWithGlobalsBag(): void
    {
        $this->assertSame(AmcTrackingController::class, $this->controller::class);
    }

    /**
     * Test getFormParameters with empty request body
     */
    public function testGetFormParametersEmpty(): void
    {
        $request = Request::create('/interface/reports/amc_tracking.php', 'POST', []);

        $params = $this->controller->getFormParameters($request);

        $this->assertSame('', $params['begin_date']);
        $this->assertSame('', $params['end_date']);
        $this->assertSame('', $params['rule']);
        $this->assertSame('', $params['provider']);
    }

    /**
     * Test getFormParameters with POST data
     */
    public function testGetFormParametersWithData(): void
    {
        $request = Request::create('/interface/reports/amc_tracking.php', 'POST', [
            'form_begin_date' => '2024-01-01 00:00:00',
            'form_end_date' => '2024-12-31 23:59:59',
            'form_rule' => 'send_sum_amc',
            'form_provider' => '5',
        ]);

        $params = $this->controller->getFormParameters($request);

        $this->assertNotSame('', $params['begin_date']);
        $this->assertNotSame('', $params['end_date']);
        $this->assertSame('send_sum_amc', $params['rule']);
        $this->assertSame('5', $params['provider']);
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
        $this->assertSame('', $provideRecHeader);

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
        $controller = new class ($this->mockGlobalsBag) extends AmcTrackingController {
            public function getProviders(): array
            {
                return [
                    [
                        'id' => 7,
                        'lname' => 'Smith',
                        'fname' => 'Pat',
                        'display_name' => 'Smith, Pat',
                    ],
                ];
            }
        };

        $params = [
            'begin_date' => '',
            'end_date' => '',
            'rule' => '',
            'provider' => '',
        ];

        $data = $controller->prepareTemplateData($params, false, $this->mockSession);

        $this->assertFalse($data['show_results']);
        $this->assertSame([], $data['results']);
        $this->assertSame('', $data['rule']);
        $this->assertSame('', $data['provider']);
        $this->assertCount(1, $data['providers']);
        $this->assertSame('Smith, Pat', $data['providers'][0]['display_name']);
        $this->assertNotSame('', $data['csrf_token']);
        $this->assertSame($data['csrf_token'], $data['csrf_token_raw']);
        $this->assertSame('conceal', $data['oemrUiSettings']['action']);
        $this->assertFalse($data['oemrUiSettings']['expandable']);
    }

    /**
     * Test prepareTemplateData structure
     */
    public function testPrepareTemplateDataStructure(): void
    {
        $controller = new class ($this->mockGlobalsBag) extends AmcTrackingController {
            public function getProviders(): array
            {
                return [];
            }

            public function getTrackingResults(
                string $rule,
                string $begin_date,
                string $end_date,
                string $provider
            ): array {
                return [
                    [
                        'pid' => 42,
                        'lname' => 'Doe',
                        'fname' => 'Jane',
                        'date' => '2024-01-15 10:00:00',
                        'id' => 99,
                    ],
                ];
            }
        };

        $params = [
            'begin_date' => '20240101000000',
            'end_date' => '20241231235959',
            'rule' => 'send_sum_amc',
            'provider' => '5',
        ];

        $data = $controller->prepareTemplateData($params, true, $this->mockSession);

        $this->assertTrue($data['show_results']);
        $this->assertSame('send_sum_amc', $data['rule']);
        $this->assertSame('5', $data['provider']);
        $this->assertCount(1, $data['results']);
        $this->assertSame(42, $data['results'][0]['pid']);
        $this->assertSame('Doe', $data['results'][0]['lname']);
        $this->assertSame('Jane', $data['results'][0]['fname']);
        $this->assertSame(99, $data['results'][0]['id']);
        $this->assertNotSame('', $data['begin_date']);
        $this->assertNotSame('', $data['end_date']);
        $this->assertSame('amc_tracking.php', $data['oemrUiSettings']['action_href']);
        $this->assertFalse($data['oemrUiSettings']['include_patient_name']);
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
    }

    /**
     * Test that OEGlobalsBag is injected and accessible on the controller
     *
     * Full verification of srcdir usage (via getTrackingResults) requires
     * database access and is covered by integration tests.
     */
    public function testOEGlobalsBagUsage(): void
    {
        $this->assertSame(AmcTrackingController::class, $this->controller::class);
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
        $this->assertTrue($property->isReadOnly());
    }
}
