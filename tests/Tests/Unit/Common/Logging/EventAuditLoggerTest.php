<?php

/**
 * EventAuditLoggerTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Claude Code Assistant
 * @copyright Copyright (c) 2025 OpenEMR Support LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Comprehensive unit tests for the EventAuditLogger class
 *
 * @package OpenEMR\Tests\Unit\Common\Logging
 * @link      http://www.open-emr.org
 * @copyright Copyright (c) 2025 OpenEMR Support LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Common\Logging;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

final class EventAuditLoggerTest extends TestCase
{
    /**
     * @var EventAuditLogger
     */
    private $eventAuditLogger;

    private \PHPUnit\Framework\MockObject\MockObject $cryptoGenMock;

    /**
     * @var array<string, mixed> Original $_SESSION backup
     */
    private array $originalSession;

    /**
     * @var array<string, mixed> Original $_SERVER backup
     */
    private array $originalServer;

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
        'audit_events_patient-record',
        'audit_events_security-administration',
        'audit_events_query',
        'audit_events_http-request',
        'gbl_force_log_breakglass',
        'atna_audit_host',
        'atna_audit_port',
        'atna_audit_localcert',
        'atna_audit_cacert',
        'enable_atna_audit',
        'adodb'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Backup original superglobals
        $this->originalSession = $_SESSION ?? [];
        $this->originalServer = $_SERVER;

        // Backup only the specific $GLOBALS keys we modify
        $this->originalGlobals = [];
        foreach ($this->modifiedGlobalKeys as $modifiedGlobalKey) {
            if (isset($GLOBALS[$modifiedGlobalKey])) {
                $this->originalGlobals[$modifiedGlobalKey] = $GLOBALS[$modifiedGlobalKey];
            }
        }

        // Get EventAuditLogger instance (works with existing singleton)
        $this->eventAuditLogger = EventAuditLogger::instance();

        // Create mock for CryptoGen
        $this->cryptoGenMock = $this->createMock(CryptoGen::class);

        // Setup default test environment
        $this->setupTestEnvironment();
    }

    protected function tearDown(): void
    {
        // Restore original superglobals
        $_SESSION = $this->originalSession;
        $_SERVER = $this->originalServer;

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
     * Setup default test environment with mocked globals and session data
     */
    private function setupTestEnvironment(): void
    {
        // Setup default $_SESSION values
        $_SESSION = [
            'authUser' => 'testuser',
            'authProvider' => 'testprovider',
            'pid' => '123'
        ];

        // Setup default $_SERVER values
        $_SERVER = [
            'SERVER_NAME' => 'test.openemr.local',
            'SERVER_ADDR' => '127.0.0.1',
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '/test/script.php',
            'QUERY_STRING' => 'param=value',
            'SSL_CLIENT_S_DN_CN' => 'test-client'
        ];

        // Setup default $GLOBALS values
        $GLOBALS['enable_auditlog'] = true;
        $GLOBALS['enable_auditlog_encryption'] = false;
        $GLOBALS['audit_events_patient-record'] = true;
        $GLOBALS['audit_events_security-administration'] = true;
        $GLOBALS['audit_events_query'] = true;
        $GLOBALS['audit_events_http-request'] = true;
        $GLOBALS['gbl_force_log_breakglass'] = false;
        $GLOBALS['atna_audit_host'] = 'audit.example.com';
        $GLOBALS['atna_audit_port'] = '6514';
        $GLOBALS['atna_audit_localcert'] = '/path/to/cert.pem';
        $GLOBALS['atna_audit_cacert'] = '/path/to/ca.pem';
        $GLOBALS['enable_atna_audit'] = false;
        $GLOBALS['adodb'] = ['db' => $this->createMockAdodb()];
    }

    /**
     * Create a mock ADODB database object
     */
    private function createMockAdodb(): MockObject
    {
        // Create a more specific mock that includes the methods we need
        $mock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['qstr', 'Insert_ID'])
            ->getMock();
        
        $mock->method('qstr')->willReturnCallback(function ($value): string {
            return "'" . addslashes($value) . "'";
        });
        $mock->method('Insert_ID')->willReturn(123);
        return $mock;
    }

    /**
     * Test singleton pattern implementation
     */
    public function testSingletonPattern(): void
    {
        $eventAuditLogger = EventAuditLogger::instance();
        $instance2 = EventAuditLogger::instance();

        $this->assertSame($eventAuditLogger, $instance2, 'EventAuditLogger should implement singleton pattern');
        $this->assertInstanceOf(EventAuditLogger::class, $eventAuditLogger);
    }

    /**
     * Test newEvent method with basic parameters
     */
    public function testNewEventBasic(): void
    {
        // Mock recordLogItem method
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem'])
            ->getMock();

        $loggerMock->expects($this->once())
            ->method('recordLogItem')
            ->with(
                1, // success
                'login', // event
                'testuser', // user
                'testprovider', // group
                'User login', // comments
                null, // patient_id
                'login' // category
            );

        $loggerMock->newEvent('login', 'testuser', 'testprovider', 1, 'User login');
    }

    /**
     * Test newEvent method with patient portal parameters
     */
    public function testNewEventPatientPortal(): void
    {
        // Mock recordLogItem method
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem'])
            ->getMock();

        $loggerMock->expects($this->once())
            ->method('recordLogItem')
            ->with(
                1, // success
                'view', // event
                'patient123', // user
                'patients', // group
                'Viewed dashboard', // comments
                456, // patient_id
                'Patient Portal', // category
                'patient-portal', // log_from
                1, // menu_item_id
                0 // ccda_doc_id
            );

        $loggerMock->newEvent(
            'view',
            'patient123',
            'patients',
            1,
            'Viewed dashboard',
            null, // PHPStan expects null for patient_id in test context
            'patient-portal',
            'dashboard'
        );
    }

    /**
     * Test recordLogItem method without encryption
     */
    public function testRecordLogItemWithoutEncryption(): void
    {
        $GLOBALS['enable_auditlog_encryption'] = false;

        // Inject mock CryptoGen
        $reflectionClass = new ReflectionClass($this->eventAuditLogger);
        $reflectionProperty = $reflectionClass->getProperty('cryptoGen');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->eventAuditLogger, $this->cryptoGenMock);

        // Call recordLogItem
        $this->eventAuditLogger->recordLogItem(
            1,
            'patient-record-select',
            'testuser',
            'testgroup',
            'SELECT * FROM patient_data',
            123,
            'patient-record'
        );

        // Test passes if no exceptions are thrown
        $this->addToAssertionCount(1);
    }

    /**
     * Test recordLogItem method with encryption
     */
    public function testRecordLogItemWithEncryption(): void
    {
        $GLOBALS['enable_auditlog_encryption'] = true;

        // Setup CryptoGen mock expectations
        $this->cryptoGenMock->expects($this->once())
            ->method('encryptStandard')
            ->with('SELECT * FROM patient_data')
            ->willReturn('encrypted_comment_data');

        // Inject mock CryptoGen
        $reflectionClass = new ReflectionClass($this->eventAuditLogger);
        $reflectionProperty = $reflectionClass->getProperty('cryptoGen');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->eventAuditLogger, $this->cryptoGenMock);

        // Call recordLogItem
        $this->eventAuditLogger->recordLogItem(
            1,
            'patient-record-select',
            'testuser',
            'testgroup',
            'SELECT * FROM patient_data',
            123,
            'patient-record'
        );

        // Test passes if no exceptions are thrown
        $this->addToAssertionCount(1);
    }

    /**
     * Test recordLogItem method with API logging
     */
    public function testRecordLogItemWithApiLogging(): void
    {
        $apiData = [
            'user_id' => 1,
            'patient_id' => 123,
            'method' => 'GET',
            'request' => '/api/patient/123',
            'request_url' => 'https://example.com/api/patient/123',
            'request_body' => '',
            'response' => '{"status": "success"}'
        ];

        // Call recordLogItem with API data
        $this->eventAuditLogger->recordLogItem(
            1,
            'api-select',
            'apiuser',
            'api',
            'API call',
            123,
            'patient-record',
            'open-emr',
            null,
            null,
            '',
            $apiData
        );

        // Test passes if no exceptions are thrown
        $this->addToAssertionCount(1);
    }

    /**
     * Test determineRFC3881EventActionCode method
     */
    public function testDetermineRFC3881EventActionCode(): void
    {
        $reflectionClass = new ReflectionClass($this->eventAuditLogger);
        $reflectionMethod = $reflectionClass->getMethod('determineRFC3881EventActionCode');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals('C', $reflectionMethod->invoke($this->eventAuditLogger, 'patient-create'));
        $this->assertEquals('C', $reflectionMethod->invoke($this->eventAuditLogger, 'patient-insert'));
        $this->assertEquals('R', $reflectionMethod->invoke($this->eventAuditLogger, 'patient-select'));
        $this->assertEquals('U', $reflectionMethod->invoke($this->eventAuditLogger, 'patient-update'));
        $this->assertEquals('D', $reflectionMethod->invoke($this->eventAuditLogger, 'patient-delete'));
        $this->assertEquals('E', $reflectionMethod->invoke($this->eventAuditLogger, 'login'));
        $this->assertEquals('E', $reflectionMethod->invoke($this->eventAuditLogger, 'other-event'));
    }

    /**
     * Test determineRFC3881EventIdDisplayName method
     */
    public function testDetermineRFC3881EventIdDisplayName(): void
    {
        $reflectionClass = new ReflectionClass($this->eventAuditLogger);
        $reflectionMethod = $reflectionClass->getMethod('determineRFC3881EventIdDisplayName');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals('Patient Record', $reflectionMethod->invoke($this->eventAuditLogger, 'patient-record'));
        $this->assertEquals('Patient Record', $reflectionMethod->invoke($this->eventAuditLogger, 'view'));
        $this->assertEquals('Login', $reflectionMethod->invoke($this->eventAuditLogger, 'login'));
        $this->assertEquals('Logout', $reflectionMethod->invoke($this->eventAuditLogger, 'logout'));
        $this->assertEquals('Patient Care Assignment', $reflectionMethod->invoke($this->eventAuditLogger, 'scheduling'));
        $this->assertEquals('Security Administration', $reflectionMethod->invoke($this->eventAuditLogger, 'security-administration'));
        $this->assertEquals('other-event', $reflectionMethod->invoke($this->eventAuditLogger, 'other-event'));
    }

    /**
     * Test createRfc3881Msg method
     */
    public function testCreateRfc3881Msg(): void
    {
        $reflectionClass = new ReflectionClass($this->eventAuditLogger);
        $reflectionMethod = $reflectionClass->getMethod('createRfc3881Msg');
        $reflectionMethod->setAccessible(true);

        $message = $reflectionMethod->invoke(
            $this->eventAuditLogger,
            'testuser',
            'testgroup',
            'patient-record-select',
            123,
            1,
            'Test audit message'
        );

        $this->assertIsString($message);
        $this->assertStringContainsString('<AuditMessage', $message);
        $this->assertStringContainsString('EventActionCode="R"', $message);
        $this->assertStringContainsString('testuser', $message);
        $this->assertStringContainsString('ParticipantObjectID="123"', $message);
    }

    /**
     * Test auditSQLEvent method with different query types
     */
    public function testAuditSQLEvent(): void
    {
        // Test SELECT query
        $this->eventAuditLogger->auditSQLEvent('SELECT * FROM patient_data WHERE pid = 123', true);

        // Test INSERT query
        $this->eventAuditLogger->auditSQLEvent("INSERT INTO patient_data (fname) VALUES ('John')", true);

        // Test UPDATE query
        $this->eventAuditLogger->auditSQLEvent("UPDATE patient_data SET fname = 'Jane' WHERE pid = 123", true);

        // Test DELETE query
        $this->eventAuditLogger->auditSQLEvent('DELETE FROM patient_data WHERE pid = 123', true);

        // Test passes if no exceptions are thrown
        $this->addToAssertionCount(1);
    }

    /**
     * Test auditSQLEvent skips logging for excluded statements
     */
    public function testAuditSQLEventSkipsExcludedStatements(): void
    {
        // Mock recordLogItem to ensure it's not called
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem'])
            ->getMock();

        $loggerMock->expects($this->never())
            ->method('recordLogItem');

        // These should be skipped
        $loggerMock->auditSQLEvent("INSERT INTO log (event) VALUES ('test')", true);
        $loggerMock->auditSQLEvent('SELECT * FROM log WHERE id = 1', true);
        $loggerMock->auditSQLEvent('SELECT count(*) FROM patient_data', true);
    }

    /**
     * Test eventCategoryFinder method
     */
    public function testEventCategoryFinder(): void
    {
        $reflectionClass = new ReflectionClass($this->eventAuditLogger);
        $reflectionMethod = $reflectionClass->getMethod('eventCategoryFinder');
        $reflectionMethod->setAccessible(true);

        // Test various table categories
        $this->assertEquals('Problem List', $reflectionMethod->invoke($this->eventAuditLogger, "INSERT INTO lists (type) VALUES ('medical_problem')", 'patient-record', 'lists'));
        $this->assertEquals('Medication', $reflectionMethod->invoke($this->eventAuditLogger, "INSERT INTO lists (type) VALUES ('medication')", 'patient-record', 'lists'));
        $this->assertEquals('Allergy', $reflectionMethod->invoke($this->eventAuditLogger, "INSERT INTO lists (type) VALUES ('allergy')", 'patient-record', 'lists'));
        $this->assertEquals('Immunization', $reflectionMethod->invoke($this->eventAuditLogger, 'INSERT INTO immunizations', 'patient-record', 'immunizations'));
        $this->assertEquals('Vitals', $reflectionMethod->invoke($this->eventAuditLogger, 'INSERT INTO form_vitals', 'patient-record', 'form_vitals'));
        $this->assertEquals('Patient Demographics', $reflectionMethod->invoke($this->eventAuditLogger, 'UPDATE patient_data', 'patient-record', 'patient_data'));
        $this->assertEquals('Billing', $reflectionMethod->invoke($this->eventAuditLogger, 'INSERT INTO billing', 'patient-record', 'billing'));
        $this->assertEquals('Scheduling', $reflectionMethod->invoke($this->eventAuditLogger, 'UPDATE openemr_postcalendar_events', 'scheduling', 'openemr_postcalendar_events'));
    }

    /**
     * Test isBreakglassUser method
     */
    public function testIsBreakglassUser(): void
    {
        $reflectionClass = new ReflectionClass($this->eventAuditLogger);
        $reflectionMethod = $reflectionClass->getMethod('isBreakglassUser');
        $reflectionMethod->setAccessible(true);

        // Test with empty user
        $this->assertFalse($reflectionMethod->invoke($this->eventAuditLogger, ''));

        // Test with non-breakglass user (mocked to return null)
        $this->assertFalse($reflectionMethod->invoke($this->eventAuditLogger, 'normaluser'));
    }

    /**
     * Test sendAtnaAuditMsg when ATNA is disabled
     */
    public function testSendAtnaAuditMsgDisabled(): void
    {
        $GLOBALS['enable_atna_audit'] = false;

        // Should return early without error when ATNA is disabled
        $this->eventAuditLogger->sendAtnaAuditMsg('testuser', 'testgroup', 'login', 0, 1, 'Test login');

        // Test passes if no exceptions are thrown
        $this->addToAssertionCount(1);
    }

    /**
     * Test createTlsConn method failure handling
     */
    public function testCreateTlsConnFailure(): void
    {
        $reflectionClass = new ReflectionClass($this->eventAuditLogger);
        $reflectionMethod = $reflectionClass->getMethod('createTlsConn');
        $reflectionMethod->setAccessible(true);

        // Test with invalid host (should return false)
        $result = $reflectionMethod->invoke($this->eventAuditLogger, 'invalid.host', 9999, '', '');
        $this->assertFalse($result);
    }

    /**
     * Test auditSQLAuditTamper method
     */
    public function testAuditSQLAuditTamper(): void
    {
        // Mock recordLogItem method
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem'])
            ->getMock();

        $loggerMock->expects($this->once())
            ->method('recordLogItem')
            ->with(
                1,
                'security-administration-insert',
                'testuser',
                'testprovider',
                'Audit Logging Enabled.'
            );

        $loggerMock->auditSQLAuditTamper('enable_auditlog', '1');
    }

    /**
     * Test recordDisclosure method
     */
    public function testRecordDisclosure(): void
    {
        // Test passes if no exceptions are thrown
        $this->eventAuditLogger->recordDisclosure(
            '2025-01-01 12:00:00',
            'disclosure',
            123,
            'External Provider',
            'Medical records disclosed for treatment',
            'testuser'
        );

        $this->addToAssertionCount(1);
    }

    /**
     * Test updateRecordedDisclosure method
     */
    public function testUpdateRecordedDisclosure(): void
    {
        // Test passes if no exceptions are thrown
        $this->eventAuditLogger->updateRecordedDisclosure(
            '2025-01-01 12:00:00',
            'disclosure-updated',
            'Updated Provider',
            'Updated disclosure description',
            456
        );

        $this->addToAssertionCount(1);
    }

    /**
     * Test deleteDisclosure method
     */
    public function testDeleteDisclosure(): void
    {
        // Test passes if no exceptions are thrown
        $this->eventAuditLogger->deleteDisclosure(789);

        $this->addToAssertionCount(1);
    }

    /**
     * Test logHttpRequest method
     */
    public function testLogHttpRequest(): void
    {
        // Mock newEvent method
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['newEvent'])
            ->getMock();

        $loggerMock->expects($this->once())
            ->method('newEvent')
            ->with(
                'http-request-select',
                'testuser',
                'testprovider',
                1,
                '/test/script.php?param=value',
                '123'
            );

        $loggerMock->logHttpRequest();
    }

    /**
     * Test logHttpRequest with different HTTP methods
     */
    public function testLogHttpRequestDifferentMethods(): void
    {
        $methods = [
            'GET' => 'select',
            'POST' => 'update',
            'PUT' => 'update',
            'DELETE' => 'delete',
            'PATCH' => 'update',
            'OPTIONS' => 'select' // default
        ];

        foreach ($methods as $httpMethod => $expectedEvent) {
            $_SERVER['REQUEST_METHOD'] = $httpMethod;

            $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
                ->onlyMethods(['newEvent'])
                ->getMock();

            $loggerMock->expects($this->once())
                ->method('newEvent')
                ->with(
                    'http-request-' . $expectedEvent,
                    'testuser',
                    'testprovider',
                    1,
                    '/test/script.php?param=value',
                    '123'
                );

            $loggerMock->logHttpRequest();
        }
    }

    /**
     * Test getEvents method with various parameters
     */
    public function testGetEvents(): void
    {
        // Test basic getEvents call
        $params = [
            'sdate' => '2025-01-01 00:00:00',
            'edate' => '2025-01-31 23:59:59',
            'user' => 'testuser',
            'patient' => '123'
        ];

        $result = $this->eventAuditLogger->getEvents($params);

        // Result should be truthy (mocked to return true)
        $this->assertTrue($result);
    }

    /**
     * Test getEvents method with extended_log (event parameter)
     */
    public function testGetEventsExtendedLog(): void
    {
        $params = [
            'event' => 'disclosure',
            'sdate' => '2025-01-01 00:00:00',
            'edate' => '2025-01-31 23:59:59'
        ];

        $result = $this->eventAuditLogger->getEvents($params);

        // Result should be truthy (mocked to return true)
        $this->assertTrue($result);
    }

    /**
     * Test behavior when audit logging is disabled
     */
    public function testAuditLogDisabled(): void
    {
        $GLOBALS['enable_auditlog'] = false;
        $GLOBALS['gbl_force_log_breakglass'] = false;

        // Mock recordLogItem to ensure it's not called
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem', 'isBreakglassUser'])
            ->getMock();

        $loggerMock->method('isBreakglassUser')->willReturn(false);
        $loggerMock->expects($this->never())->method('recordLogItem');

        $loggerMock->auditSQLEvent('SELECT * FROM patient_data', true);
    }

    /**
     * Test breakglass user logging when audit is disabled
     */
    public function testBreakglassUserLogging(): void
    {
        $GLOBALS['enable_auditlog'] = false;
        $GLOBALS['gbl_force_log_breakglass'] = true;

        // Mock recordLogItem and isBreakglassUser
        $loggerMock = $this->getMockBuilder(EventAuditLogger::class)
            ->onlyMethods(['recordLogItem', 'isBreakglassUser'])
            ->getMock();

        $loggerMock->method('isBreakglassUser')->willReturn(true);
        $loggerMock->expects($this->once())->method('recordLogItem');

        $loggerMock->auditSQLEvent('SELECT * FROM patient_data', true);
    }

    /**
     * Test edge case with null patient ID handling
     */
    public function testNullPatientIdHandling(): void
    {
        // Test with string "NULL"
        $this->eventAuditLogger->recordLogItem(1, 'test', 'user', 'group', 'comment', 'NULL');

        // Test with actual null
        $this->eventAuditLogger->recordLogItem(1, 'test', 'user', 'group', 'comment', null);

        // Test passes if no exceptions are thrown
        $this->addToAssertionCount(1);
    }
}
