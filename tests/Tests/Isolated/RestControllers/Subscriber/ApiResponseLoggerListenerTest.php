<?php

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\Audit\ApiLogRedactor;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\Subscriber\ApiResponseLoggerListener;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class ApiResponseLoggerListenerTest extends TestCase
{
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
                    'client_id' => '',
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
        $apiResponseLoggerListener->setSystemLogger($this->createMock(LoggerInterface::class));
        $apiResponseLoggerListener->setEventAuditLogger($auditLogger);
        $apiResponseLoggerListener->onRequestTerminated($terminatedEvent);
    }

    /**
     * Full-logging mode must capture the request body and the response body
     * separately, so an auditor can see both what the client sent and what
     * the server replied with. The two columns are no longer identical.
     *
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
        $requestBodyJson = json_encode(['resourceType' => 'Patient', 'name' => [['family' => 'Smith']]]);
        $this->assertIsString($requestBodyJson);
        $request = HttpRestRequest::create(
            '/apis/default/fhir/Patient',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/fhir+json'],
            $requestBodyJson
        );
        $mockSessionFactory = new MockFileSessionStorageFactory();
        $session = new Session($mockSessionFactory->createStorage(null));
        $session->set('authUser', 'test_user');
        $session->set('authUserID', 1);
        $session->set('authProvider', 'Default');
        $session->set('pid', 123); // Set a patient ID for testing
        $request->setSession($session);
        $request->setResource('Patient');
        $request->setClientId('test-oauth-client');

        $jsonDataResponse = [
            'id' => 42,
            'uuid' => '00000000-0000-0000-0000-000000000042',
        ];
        $encodedResponseJson = json_encode($jsonDataResponse);
        $response = new JsonResponse($jsonDataResponse, Response::HTTP_OK);
        $terminatedEvent = new TerminateEvent($kernel, $request, $response);
        $auditLogger = $this->createMock(EventAuditLogger::class);
        $auditLogger
            ->method('recordLogItem')
            ->withAnyParameters()
            ->willReturnCallback(function ($success, $event, $user, $group, $comments, $patientId, $category, $logFrom, $menuItemId, $ccdaDocId, $user_notes, $api)
 use ($session, $request, $requestBodyJson, $encodedResponseJson): void {
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
                    'client_id' => 'test-oauth-client',
                    'patient_id' => $session->get('pid'),
                    'method' => $request->getMethod(),
                    'request' => $request->getResource(),
                    'request_url' => $request->getUri(),
                    'request_body' => $requestBodyJson,
                    'response' => $encodedResponseJson,
                ], $api, 'request_body and response columns must each carry their own payload');
                // void return
            });
        $apiResponseLoggerListener = new ApiResponseLoggerListener();
        $apiResponseLoggerListener->setSystemLogger($this->createMock(LoggerInterface::class));
        $apiResponseLoggerListener->setEventAuditLogger($auditLogger);
        $apiResponseLoggerListener->onRequestTerminated($terminatedEvent);
    }

    /**
     * StreamedResponse::getContent() returns false rather than the content.
     * The listener must normalize that to an empty string so the audit row
     * (and its checksum) never receives a boolean.
     *
     * @return void
     * @throws Exception
     */
    public function testOnRequestTerminatedWithStreamedResponseLogsEmptyBody(): void
    {
        $globalsBag = new OEGlobalsBag([
            'api_log_option' => 2, // response logging enabled: exercises the getContent() path
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
        $session->set('pid', 123);
        $request->setSession($session);
        $request->setResource('test');
        $request->setClientId('test-oauth-client');

        $response = new StreamedResponse(function (): void {
            echo '{"status": "streamed"}';
        }, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        $terminatedEvent = new TerminateEvent($kernel, $request, $response);
        $auditLogger = $this->createMock(EventAuditLogger::class);
        $auditLogger
            ->expects($this->once())
            ->method('recordLogItem')
            ->withAnyParameters()
            ->willReturnCallback(function ($success, $event, $user, $group, $comments, $patientId, $category, $logFrom, $menuItemId, $ccdaDocId, $user_notes, $api): void {
                $this->assertIsArray($api);
                $this->assertSame('', $api['request_body'], 'GET request has no body — must be logged as empty string');
                $this->assertSame('', $api['response'], 'Streamed response must be logged as empty string');
                $this->assertSame('test-oauth-client', $api['client_id'], 'Client id must still be recorded for streamed responses');
                // void return
            });
        $apiResponseLoggerListener = new ApiResponseLoggerListener();
        $apiResponseLoggerListener->setLogger($this->createMock(LoggerInterface::class));
        $apiResponseLoggerListener->setEventAuditLogger($auditLogger);
        $apiResponseLoggerListener->onRequestTerminated($terminatedEvent);
    }

    /**
     * Request bodies with content-types outside the whitelist (file uploads,
     * NDJSON bulk-import, FHIR Binary POSTs) must not land in api_log — the
     * same way binary/streamed responses already don't.
     *
     * @throws Exception
     */
    public function testOnRequestTerminatedDropsRequestBodyForDisallowedContentType(): void
    {
        $globalsBag = new OEGlobalsBag(['api_log_option' => 2]);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')->willReturn($globalsBag);

        $request = HttpRestRequest::create(
            '/apis/default/fhir/Binary',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/octet-stream'],
            "\x00\x01\x02binary-payload"
        );
        $session = new Session((new MockFileSessionStorageFactory())->createStorage(null));
        $request->setSession($session);

        $response = new JsonResponse(['id' => 7], Response::HTTP_OK);
        $terminatedEvent = new TerminateEvent($kernel, $request, $response);

        $auditLogger = $this->createMock(EventAuditLogger::class);
        $auditLogger
            ->expects($this->once())
            ->method('recordLogItem')
            ->willReturnCallback(function ($success, $event, $user, $group, $comments, $patientId, $category, $logFrom, $menuItemId, $ccdaDocId, $user_notes, $api): void {
                $this->assertIsArray($api);
                $this->assertSame('', $api['request_body'], 'Non-whitelisted content-type request body must not be captured');
                $this->assertSame(json_encode(['id' => 7]), $api['response']);
            });

        $listener = new ApiResponseLoggerListener();
        $listener->setLogger($this->createMock(LoggerInterface::class));
        $listener->setEventAuditLogger($auditLogger);
        $listener->onRequestTerminated($terminatedEvent);
    }

    /**
     * OAuth2 token endpoint carries transient runtime fields in both
     * directions. The request's password / client_secret / code_verifier
     * and the response's access_token / refresh_token are replaced with
     * the sentinel before the row is written.
     *
     * @throws Exception
     */
    public function testOnRequestTerminatedRedactsOAuth2TokenRequest(): void
    {
        $globalsBag = new OEGlobalsBag(['api_log_option' => 2]);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')->willReturn($globalsBag);

        $request = HttpRestRequest::create(
            '/oauth2/default/token',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
            'grant_type=password&username=admin&password=hunter2&client_id=cid&client_secret=supersecret'
        );
        $session = new Session((new MockFileSessionStorageFactory())->createStorage(null));
        $request->setSession($session);

        $response = new JsonResponse([
            'access_token' => 'eyJhbGciOi...',
            'refresh_token' => 'rt-abc',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], Response::HTTP_OK);
        $terminatedEvent = new TerminateEvent($kernel, $request, $response);

        $auditLogger = $this->createMock(EventAuditLogger::class);
        $auditLogger
            ->expects($this->once())
            ->method('recordLogItem')
            ->willReturnCallback(function ($success, $event, $user, $group, $comments, $patientId, $category, $logFrom, $menuItemId, $ccdaDocId, $user_notes, $api): void {
                $this->assertIsArray($api);
                $this->assertIsString($api['request_body']);
                $this->assertIsString($api['response']);
                parse_str($api['request_body'], $parsedRequest);
                $this->assertSame(ApiLogRedactor::SENTINEL, $parsedRequest['password'] ?? null);
                $this->assertSame(ApiLogRedactor::SENTINEL, $parsedRequest['client_secret'] ?? null);
                $this->assertSame('admin', $parsedRequest['username'] ?? null, 'descriptive fields pass through');
                $this->assertSame('password', $parsedRequest['grant_type'] ?? null);

                $decodedResponse = json_decode($api['response'], true);
                $this->assertIsArray($decodedResponse);
                $this->assertSame(ApiLogRedactor::SENTINEL, $decodedResponse['access_token']);
                $this->assertSame(ApiLogRedactor::SENTINEL, $decodedResponse['refresh_token']);
                $this->assertSame('Bearer', $decodedResponse['token_type']);
                $this->assertSame(3600, $decodedResponse['expires_in']);
            });

        $listener = new ApiResponseLoggerListener();
        $listener->setLogger($this->createMock(LoggerInterface::class));
        $listener->setEventAuditLogger($auditLogger);
        $listener->onRequestTerminated($terminatedEvent);
    }

    /**
     * OAuth2 Dynamic Client Registration response includes freshly issued
     * client_secret and registration_access_token values that are replaced
     * with the sentinel before the row is written. The request body carries
     * only descriptive metadata and passes through unchanged.
     *
     * @throws Exception
     */
    public function testOnRequestTerminatedRedactsOAuth2RegistrationResponse(): void
    {
        $globalsBag = new OEGlobalsBag(['api_log_option' => 2]);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')->willReturn($globalsBag);

        $registrationRequest = json_encode([
            'client_name' => 'test app',
            'redirect_uris' => ['https://localhost/cb'],
        ]);
        $this->assertIsString($registrationRequest);

        $request = HttpRestRequest::create(
            '/oauth2/default/registration',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $registrationRequest
        );
        $session = new Session((new MockFileSessionStorageFactory())->createStorage(null));
        $request->setSession($session);

        $response = new JsonResponse([
            'client_id' => 'cid',
            'client_secret' => 'supersecret',
            'registration_access_token' => 'ratoken',
            'client_name' => 'test app',
        ], Response::HTTP_OK);
        $terminatedEvent = new TerminateEvent($kernel, $request, $response);

        $auditLogger = $this->createMock(EventAuditLogger::class);
        $auditLogger
            ->expects($this->once())
            ->method('recordLogItem')
            ->willReturnCallback(function ($success, $event, $user, $group, $comments, $patientId, $category, $logFrom, $menuItemId, $ccdaDocId, $user_notes, $api) use ($registrationRequest): void {
                $this->assertIsArray($api);
                $this->assertIsString($api['response']);
                $this->assertSame(
                    $registrationRequest,
                    $api['request_body'],
                    'Registration request carries only descriptive metadata — must pass through unchanged'
                );

                $decodedResponse = json_decode($api['response'], true);
                $this->assertIsArray($decodedResponse);
                $this->assertSame(ApiLogRedactor::SENTINEL, $decodedResponse['client_secret']);
                $this->assertSame(ApiLogRedactor::SENTINEL, $decodedResponse['registration_access_token']);
                $this->assertSame('cid', $decodedResponse['client_id']);
                $this->assertSame('test app', $decodedResponse['client_name']);
            });

        $listener = new ApiResponseLoggerListener();
        $listener->setLogger($this->createMock(LoggerInterface::class));
        $listener->setEventAuditLogger($auditLogger);
        $listener->onRequestTerminated($terminatedEvent);
    }
}
