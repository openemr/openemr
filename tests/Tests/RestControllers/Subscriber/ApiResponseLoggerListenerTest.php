<?php

namespace OpenEMR\Tests\RestControllers\Subscriber;

use Monolog\Level;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Subscriber\ApiResponseLoggerListener;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class ApiResponseLoggerListenerTest extends TestCase
{
    const LOG_LEVEL = Level::Critical;
    /**
     * @return void
     * @throws Exception
     */
    public function testOnRequestTerminatedWithApiLogOption1(): void
    {
        $globalsBag = new OEGlobalsBag([
            'api_log_option' => 1, // Set to 1 to skip logging the response
        ]);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')
            ->willReturn($globalsBag);
        $request = HttpRestRequest::create('/api/test');
        $mockSessionFactory = new MockFileSessionStorageFactory();
        $session = new Session($mockSessionFactory->createStorage(null));
        $session->set('authUser', 'test_user');
        $session->set('authUserID', 1);
        $session->set('authProvider', 'Default');
        $request->setSession($session);

        $response = new Response('', Response::HTTP_OK);
        $terminatedEvent = new TerminateEvent($kernel, $request, $response);

        $auditLogger = $this->createMock(EventAuditLogger::class);
        $auditLogger
            ->method('recordLogItem')
            ->withAnyParameters()
            ->willReturnCallback(function ($success, $event, $user, $group, $comments, $patientId, $category, $logFrom, $menuItemId, $ccdaDocId, $user_notes, $api)
 use ($session, $request): void {
                $this->assertEquals(1, $success, 'Success should be 1');
                $this->assertEquals('api', $event, 'Event should be "api"');
                $this->assertEquals($session->get('authUser'), $user, 'User should have been set');
                $this->assertEquals($session->get('authProvider'), $group, 'Group should be empty');
                $this->assertEquals('api log', $comments, 'Comments should be "api log"');
                $this->assertEquals($session->get('pid'), $patientId, 'Patient ID should be set');
                $this->assertEquals('api', $category, 'Category should be "api"');
                $this->assertEquals('open-emr', $logFrom, 'Log from should be "open-emr"');
                $this->assertNull($menuItemId, 'Menu item ID should be null');
                $this->assertNull($ccdaDocId, 'CCDA Doc ID should be null');
                $this->assertEquals('', $user_notes, 'User notes should be empty');
                $this->assertEquals([
                    'user_id' => $session->get('authUserID'),
                    'patient_id' => $session->get('pid'),
                    'method' => $request->getMethod(),
                    'request' => $request->getResource(),
                    'request_url' => $request->getUri(),
                    'request_body' => '',
                    'response' => ''
                ], $api, 'API values should have been set correctly');
                // void return
            });
        $apiResponseLoggerListener = new ApiResponseLoggerListener();
        $apiResponseLoggerListener->setSystemLogger(new SystemLogger(self::LOG_LEVEL));
        $apiResponseLoggerListener->setEventAuditLogger($auditLogger);
        $apiResponseLoggerListener->onRequestTerminated($terminatedEvent);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testOnRequestTerminatedWithApiLogOption2(): void
    {
        $globalsBag = new OEGlobalsBag([
            'api_log_option' => 2, // Set to 2 to log the response
        ]);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')
            ->willReturn($globalsBag);
        $request = HttpRestRequest::create('/api/test');
        $mockSessionFactory = new MockFileSessionStorageFactory();
        $session = new Session($mockSessionFactory->createStorage(null));
        $session->set('authUser', 'test_user');
        $session->set('authUserID', 1);
        $session->set('authProvider', 'Default');
        $session->set('pid', 123); // Set a patient ID for testing
        $request->setSession($session);
        $request->setResource('test');

        $jsonDataResponse = [
            'message' => 'Test response',
            'data' => ['key' => 'value']
        ];
        $encodedJson = json_encode($jsonDataResponse);
        $response = new JsonResponse($jsonDataResponse, Response::HTTP_OK);
        $terminatedEvent = new TerminateEvent($kernel, $request, $response);
        $auditLogger = $this->createMock(EventAuditLogger::class);
        $auditLogger
            ->method('recordLogItem')
            ->withAnyParameters()
            ->willReturnCallback(function ($success, $event, $user, $group, $comments, $patientId, $category, $logFrom, $menuItemId, $ccdaDocId, $user_notes, $api)
 use ($session, $request, $encodedJson): void {
                $this->assertEquals(1, $success, 'Success should be 1');
                $this->assertEquals('api', $event, 'Event should be "api"');
                $this->assertEquals($session->get('authUser'), $user, 'User should have been set');
                $this->assertEquals($session->get('authProvider'), $group, 'Group should be empty');
                $this->assertEquals('api log', $comments, 'Comments should be "api log"');
                $this->assertEquals($session->get('pid'), $patientId, 'Patient ID should be set');
                $this->assertEquals('api', $category, 'Category should be "api"');
                $this->assertEquals('open-emr', $logFrom, 'Log from should be "open-emr"');
                $this->assertNull($menuItemId, 'Menu item ID should be null');
                $this->assertNull($ccdaDocId, 'CCDA Doc ID should be null');
                $this->assertEquals('', $user_notes, 'User notes should be empty');
                $this->assertEquals([
                    'user_id' => $session->get('authUserID'),
                    'patient_id' => $session->get('pid'),
                    'method' => $request->getMethod(),
                    'request' => $request->getResource(),
                    'request_url' => $request->getUri(),
                    'request_body' => $encodedJson,
                    'response' => $encodedJson
                ], $api, 'API values should have been set correctly');
                // void return
            });
        $apiResponseLoggerListener = new ApiResponseLoggerListener();
        $apiResponseLoggerListener->setSystemLogger(new SystemLogger(self::LOG_LEVEL));
        $apiResponseLoggerListener->setEventAuditLogger($auditLogger);
        $apiResponseLoggerListener->onRequestTerminated($terminatedEvent);
    }
}
