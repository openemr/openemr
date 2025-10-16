<?php

/**
 * Fixtures tests for the EventAuditLogger class
 * These tests use database fixtures to provide comprehensive coverage
 * of database-dependent functionality like the patient portal menu lookup
 *
 * @package   OpenEMR\Tests\Fixtures
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

final class EventAuditLoggerFixturesTest extends TestCase
{
    /**
     * @var EventAuditLogger
     */
    private $eventAuditLogger;

    /**
     * @var array<string, mixed> Original $GLOBALS backup
     */
    private array $originalGlobals;

    /**
     * @var array<int, string> Keys of $GLOBALS that we modify in tests
     */
    private array $modifiedGlobalKeys = [
        'enable_auditlog',
        'enable_auditlog_encryption',
        'enable_atna_audit'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Backup only the specific $GLOBALS keys we modify
        $this->originalGlobals = [];
        foreach ($this->modifiedGlobalKeys as $modifiedGlobalKey) {
            if (isset($GLOBALS[$modifiedGlobalKey])) {
                $this->originalGlobals[$modifiedGlobalKey] = $GLOBALS[$modifiedGlobalKey];
            }
        }

        // Enable audit logging for comprehensive testing
        $GLOBALS['enable_auditlog'] = true;
        $GLOBALS['enable_auditlog_encryption'] = false;

        $this->eventAuditLogger = EventAuditLogger::instance();

        // Install patient portal menu fixtures
        $this->installPatientPortalMenuFixtures();
    }

    protected function tearDown(): void
    {
        // Clean up fixtures
        $this->removePatientPortalMenuFixtures();

        // Restore original $GLOBALS values and unset any we added
        foreach ($this->modifiedGlobalKeys as $modifiedGlobalKey) {
            if (isset($this->originalGlobals[$modifiedGlobalKey])) {
                $GLOBALS[$modifiedGlobalKey] = $this->originalGlobals[$modifiedGlobalKey];
            } elseif (isset($GLOBALS[$modifiedGlobalKey])) {
                unset($GLOBALS[$modifiedGlobalKey]);
            }
        }

        parent::tearDown();
    }

    /**
     * Install patient portal menu fixtures for testing
     */
    private function installPatientPortalMenuFixtures(): void
    {
        $fixtures = [
            [
                'patient_portal_menu_id' => 1001,
                'patient_portal_menu_group_id' => 1,
                'menu_name' => 'dashboard',
                'menu_order' => 1,
                'menu_status' => 1
            ],
            [
                'patient_portal_menu_id' => 1002,
                'patient_portal_menu_group_id' => 1,
                'menu_name' => 'appointments',
                'menu_order' => 2,
                'menu_status' => 1
            ],
            [
                'patient_portal_menu_id' => 1003,
                'patient_portal_menu_group_id' => 1,
                'menu_name' => 'messages',
                'menu_order' => 3,
                'menu_status' => 1
            ],
            [
                'patient_portal_menu_id' => 1004,
                'patient_portal_menu_group_id' => 1,
                'menu_name' => 'demographics',
                'menu_order' => 4,
                'menu_status' => 1
            ]
        ];

        foreach ($fixtures as $fixture) {
            $sql = "INSERT INTO patient_portal_menu (patient_portal_menu_id, patient_portal_menu_group_id, menu_name, menu_order, menu_status) VALUES (?, ?, ?, ?, ?)";
            $binds = [
                $fixture['patient_portal_menu_id'],
                $fixture['patient_portal_menu_group_id'],
                $fixture['menu_name'],
                $fixture['menu_order'],
                $fixture['menu_status']
            ];
            QueryUtils::sqlInsert($sql, $binds);
        }
    }

    /**
     * Remove patient portal menu fixtures
     */
    private function removePatientPortalMenuFixtures(): void
    {
        $sql = "DELETE FROM patient_portal_menu WHERE patient_portal_menu_id IN (1001, 1002, 1003, 1004)";
        sqlStatement($sql);
    }

    /**
     * Test newEvent patient portal path with real database fixtures
     * This test covers the sqlFetchArray loop and menu lookup logic with actual data
     */
    public function testNewEventPatientPortalWithFixtures(): void
    {
        // Create a partial mock to capture recordLogItem calls
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem'])
            ->getMock();

        // Expect recordLogItem to be called with the correct menu_item_id
        $loggerMock->expects($this->once())
            ->method('recordLogItem')
            ->with(
                1, // success
                'view', // event
                'patient123', // user
                'patients', // groupname
                'Viewed patient appointments', // comments
                null, // patient_id
                'Patient Portal', // category
                'patient-portal', // log_from
                1002, // menu_item_id - should find 'appointments' and resolve to 1002
                0, // ccda_doc_id
                '', // crt_user
                null // ip_address
            );

        // Execute the patient portal newEvent - this will:
        // 1. Take the patient-portal path (log_from == 'patient-portal')
        // 2. Execute sqlStatement to query patient_portal_menu table
        // 3. Loop through results with sqlFetchArray (covers the for loop)
        // 4. Build the $menuItems array: $menuItems[$rowMenuItem['patient_portal_menu_id']] = $rowMenuItem['menu_name']
        // 5. Use array_search to find 'appointments' in $menuItems and get its key
        // 6. Call recordLogItem with the resolved menu_item_id
        $loggerMock->newEvent(
            'view',
            'patient123',
            'patients',
            1,
            'Viewed patient appointments',
            null,
            'patient-portal',
            'appointments'  // This should be found in our fixtures as menu_item_id 1002
        );
    }

    /**
     * Test newEvent patient portal path with different menu items
     */
    public function testNewEventPatientPortalDifferentMenuItems(): void
    {
        // Test dashboard lookup
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem'])
            ->getMock();

        $loggerMock->expects($this->once())
            ->method('recordLogItem')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                1001, // menu_item_id for 'dashboard'
                $this->anything(),
                $this->anything(),
                $this->anything()
            );

        $loggerMock->newEvent(
            'view',
            'patient123',
            'patients',
            1,
            'Viewed dashboard',
            null,
            'patient-portal',
            'dashboard'
        );

        // Test messages lookup
        $loggerMock2 = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem'])
            ->getMock();

        $loggerMock2->expects($this->once())
            ->method('recordLogItem')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                1003, // menu_item_id for 'messages'
                $this->anything(),
                $this->anything(),
                $this->anything()
            );

        $loggerMock2->newEvent(
            'view',
            'patient123',
            'patients',
            1,
            'Viewed messages',
            null,
            'patient-portal',
            'messages'
        );
    }

    /**
     * Test newEvent patient portal path with nonexistent menu item
     */
    public function testNewEventPatientPortalNonexistentMenuItem(): void
    {
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem'])
            ->getMock();

        // When menu item is not found, array_search returns false
        $loggerMock->expects($this->once())
            ->method('recordLogItem')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                false, // menu_item_id should be false for nonexistent item
                $this->anything(),
                $this->anything(),
                $this->anything()
            );

        $loggerMock->newEvent(
            'view',
            'patient123',
            'patients',
            1,
            'Accessed unknown portal section',
            null,
            'patient-portal',
            'nonexistent'
        );
    }

    /**
     * Test that the patient portal path is properly covered with real menu data
     * This ensures the sqlFetchArray loop executes with multiple iterations
     */
    public function testPatientPortalMenuLoopExecution(): void
    {
        // Execute newEvent without mocking to ensure the real database path works
        $this->eventAuditLogger->newEvent(
            'view',
            'test_user',
            'test_provider',
            1,
            'Test portal access for coverage',
            null,
            'patient-portal',
            'demographics'
        );

        // If we reach this point without exceptions, the database path worked
        // This covers the sqlStatement call, the for loop with sqlFetchArray,
        // the menuItems array building, and the array_search lookup
        $this->addToAssertionCount(1);
    }
}
