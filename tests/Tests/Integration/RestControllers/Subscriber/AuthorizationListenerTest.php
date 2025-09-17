<?php

namespace OpenEMR\Tests\Integration\RestControllers\Subscriber;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ResourceScopeEntityList;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopePermissionObject;
use OpenEMR\Common\Auth\OpenIDConnect\Validators\ScopeValidatorFactory;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent;
use OpenEMR\RestControllers\Authorization\IAuthorizationStrategy;
use OpenEMR\RestControllers\Subscriber\AuthorizationListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthorizationListenerTest extends TestCase
{
    private AuthorizationListener $authListener;
    private SystemLogger $mockLogger;
    private OEGlobalsBag $mockGlobalsBag;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->mockLogger = $this->createMock(SystemLogger::class);
        $this->mockGlobalsBag = $this->createMock(OEGlobalsBag::class);

        $this->authListener = new AuthorizationListener()   ;
        $this->authListener->setLogger($this->mockLogger);
        $this->authListener->setGlobals($this->mockGlobalsBag);

        // Clear default strategies to have clean test environment
        $this->authListener->clearAuthorizationStrategies();
    }

    public function testGetSubscribedEvents(): void
    {
        $events = AuthorizationListener::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertArrayHasKey(RestApiSecurityCheckEvent::EVENT_HANDLE, $events);

        // Verify the method and priority are correct
        $this->assertEquals([['onKernelRequest', 50]], $events[KernelEvents::REQUEST]);
        $this->assertEquals([['onRestApiSecurityCheck', 50]], $events[RestApiSecurityCheckEvent::EVENT_HANDLE]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testOnKernelRequestSkipsProcessing(): void
    {
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->expects($this->atLeastOnce())
            ->method('getSystemLogger')
            ->willReturn($this->mockLogger);
        $kernel->expects($this->atLeastOnce())
            ->method('getGlobalsBag')
            ->willReturn($this->mockGlobalsBag);

        $mockStrategy = $this->createMock(IAuthorizationStrategy::class);
        $mockStrategy->expects($this->once())
            ->method('shouldProcessRequest')
            ->willReturn(false);

        $this->authListener->addAuthorizationStrategy($mockStrategy);

        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent->expects($this->atLeastOnce())
            ->method('getKernel')
            ->willReturn($kernel);

        $requestEvent->expects($this->atLeastOnce())
            ->method('getRequest')
            ->willReturn($this->createMock(HttpRestRequest::class));

        // Should not throw any exceptions when no strategy processes the request
        $this->authListener->onKernelRequest($requestEvent);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testOnKernelRequestProcessesSecondStrategyIfFirstIsSkipped(): void
    {
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->expects($this->atLeastOnce())
            ->method('getSystemLogger')
            ->willReturn($this->mockLogger);
        $kernel->expects($this->atLeastOnce())
            ->method('getGlobalsBag')
            ->willReturn($this->mockGlobalsBag);

        $mockStrategy1 = $this->createMock(IAuthorizationStrategy::class);
        $mockStrategy1->expects($this->atLeastOnce())
            ->method('shouldProcessRequest')
            ->willReturn(false);

        $mockStrategy2 = $this->createMock(IAuthorizationStrategy::class);
        $mockStrategy2->expects($this->atLeastOnce())
            ->method('shouldProcessRequest')
            ->willReturn(true);
        $mockStrategy2->expects($this->once())
            ->method('authorizeRequest')
            ->willReturn(true);

        $this->authListener->addAuthorizationStrategy($mockStrategy1);
        $this->authListener->addAuthorizationStrategy($mockStrategy2);

        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent->expects($this->atLeastOnce())
            ->method('getKernel')
            ->willReturn($kernel);
        $requestEvent->expects($this->atLeastOnce())
            ->method('getRequest')
            ->willReturn($this->createMock(HttpRestRequest::class));

        $this->authListener->onKernelRequest($requestEvent);
    }


    /**
     * Test that security check is skipped when the event indicates it should be skipped
     * @return void
     * @throws Exception
     * @throws AccessDeniedException
     */
    public function testOnRestApiSecurityCheckSkipsWhenEventIndicates(): void
    {
        $mockRestRequest = $this->createMock(HttpRestRequest::class);

        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->once())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(true);
        $event->expects($this->once())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);

        $result = $this->authListener->onRestApiSecurityCheck($event);

        $this->assertSame($event, $result);
    }

    /**
     * Test patient request requires patient UUID
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckPatientRequestRequiresUuid(): void
    {
        $mockRestRequest = $this->createMock(HttpRestRequest::class);
        $mockRestRequest->expects($this->once())
            ->method('isPatientRequest')
            ->willReturn(true);/**
     *
     */
        $mockRestRequest->expects($this->once())
            ->method('getPatientUUIDString')
            ->willReturn(null); // No UUID provided

        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->once())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(false);
        $event->expects($this->atLeastOnce())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);
        $event->expects($this->once())
            ->method('getScopeType')
            ->willReturn('user');

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Patient UUID is required for patient requests.');

        $this->authListener->onRestApiSecurityCheck($event);
    }

    /**
     * Test local API request skips authorization
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckSkipsForLocalApi(): void
    {
        $mockRestRequest = $this->createMock(HttpRestRequest::class);
        $mockRestRequest->expects($this->once())
            ->method('isPatientRequest')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isLocalApi')
            ->willReturn(true);

        $this->mockLogger->expects($this->once())
            ->method('debug')
            ->with('Skipping authorization for request', ['request' => $mockRestRequest]);

        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->once())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(false);
        $event->expects($this->atLeastOnce())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);
        $event->expects($this->once())
            ->method('getScopeType')
            ->willReturn('user');

        $result = $this->authListener->onRestApiSecurityCheck($event);

        $this->assertSame($event, $result);
    }

    /**
     * Test skip authorization attribute
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckSkipsForSkipAuthorizationAttribute(): void
    {
        $mockAttributes = $this->createMock(ParameterBag::class);
        $mockAttributes->expects($this->once())
            ->method('get')
            ->with('skipAuthorization', false)
            ->willReturn(true);

        $mockRestRequest = $this->createMock(HttpRestRequest::class);
        $mockRestRequest->expects($this->once())
            ->method('isPatientRequest')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isLocalApi')
            ->willReturn(false);
        $mockRestRequest->attributes = $mockAttributes;

        $this->mockLogger->expects($this->once())
            ->method('debug')
            ->with('Skipping authorization for request', ['request' => $mockRestRequest]);

        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->once())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(false);
        $event->expects($this->atLeastOnce())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);
        $event->expects($this->once())
            ->method('getScopeType')
            ->willReturn('user');

        $result = $this->authListener->onRestApiSecurityCheck($event);

        $this->assertSame($event, $result);
    }

    /**
     * Test FHIR patient write request denied for patient role
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckDeniesPatientWriteForPatientRole(): void
    {
        $mockAttributes = $this->createMock(ParameterBag::class);
        $mockAttributes->expects($this->once())
            ->method('get')
            ->with('skipAuthorization', false)
            ->willReturn(false);

        $mockRestRequest = $this->createMock(HttpRestRequest::class);
        $mockRestRequest->expects($this->once())
            ->method('isPatientRequest')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isLocalApi')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isFhir')
            ->willReturn(true);
        $mockRestRequest->expects($this->once())
            ->method('isPatientWriteRequest')
            ->willReturn(true);
        $mockRestRequest->expects($this->once())
            ->method('getRequestUserRole')
            ->willReturn('patient');
        $mockRestRequest->attributes = $mockAttributes;

        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->once())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(false);
        $event->expects($this->atLeastOnce())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);
        $event->expects($this->once())
            ->method('getScopeType')
            ->willReturn('patient');

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Patient user role is not allowed to write FHIR resources.');

        $this->authListener->onRestApiSecurityCheck($event);
    }

    /**
     * Test standard API request requires users role
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckDeniesStandardApiForNonUsersRole(): void
    {
        $mockAttributes = $this->createMock(ParameterBag::class);
        $mockAttributes->expects($this->once())
            ->method('get')
            ->with('skipAuthorization', false)
            ->willReturn(false);

        $mockRestRequest = $this->createMock(HttpRestRequest::class);
        $mockRestRequest->expects($this->once())
            ->method('isPatientRequest')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isLocalApi')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isFhir')
            ->willReturn(false);
        $mockRestRequest->expects($this->atLeastOnce())
            ->method('isStandardApiRequest')
            ->willReturn(true);
        $mockRestRequest->expects($this->once())
            ->method('getRequestUserRole')
            ->willReturn('patient');
        $mockRestRequest->attributes = $mockAttributes;

        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->atLeastOnce())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(false);
        $event->expects($this->atLeastOnce())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);
        $event->expects($this->once())
            ->method('getScopeType')
            ->willReturn('patient');

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('not allowing patient or system role to access oemr api');

        $this->authListener->onRestApiSecurityCheck($event);
    }

    /**
     * Test portal request requires patient role
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckDeniesPortalForNonPatientRole(): void
    {
        $mockAttributes = $this->createMock(ParameterBag::class);
        $mockAttributes->expects($this->once())
            ->method('get')
            ->with('skipAuthorization', false)
            ->willReturn(false);

        $mockRestRequest = $this->createMock(HttpRestRequest::class);
        $mockRestRequest->expects($this->once())
            ->method('isPatientRequest')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isLocalApi')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isFhir')
            ->willReturn(false);
        $mockRestRequest->expects($this->atLeastOnce())
            ->method('isStandardApiRequest')
            ->willReturn(false);
        $mockRestRequest->expects($this->atLeastOnce())
            ->method('isPortalRequest')
            ->willReturn(true);
        $mockRestRequest->expects($this->once())
            ->method('getRequestUserRole')
            ->willReturn('users');
        $mockRestRequest->attributes = $mockAttributes;

        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->once())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(false);
        $event->expects($this->atLeastOnce())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);
        $event->expects($this->once())
            ->method('getScopeType')
            ->willReturn('user');

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('not allowing non-patient role to access port api');

        $this->authListener->onRestApiSecurityCheck($event);
    }

    /**
     * Test invalid request type throws exception
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckDeniesInvalidRequestType(): void
    {
        $mockAttributes = $this->createMock(ParameterBag::class);
        $mockAttributes->expects($this->once())
            ->method('get')
            ->with('skipAuthorization', false)
            ->willReturn(false);

        $mockRestRequest = $this->createMock(HttpRestRequest::class);
        $mockRestRequest->expects($this->once())
            ->method('isPatientRequest')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isLocalApi')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isFhir')
            ->willReturn(false);
        $mockRestRequest->expects($this->once())
            ->method('isStandardApiRequest')
            ->willReturn(false);
        $mockRestRequest->expects($this->atLeastOnce())
            ->method('isPortalRequest')
            ->willReturn(false);
        $mockRestRequest->attributes = $mockAttributes;

        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->once())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(false);
        $event->expects($this->atLeastOnce())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);
        $event->expects($this->once())
            ->method('getScopeType')
            ->willReturn('user');

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('not allowing invalid role');

        $this->authListener->onRestApiSecurityCheck($event);
    }

    /**
     * Test scope checking without resource
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckValidatesScopeWithoutResource(): void
    {
        $mockScope = ScopeEntity::createFromString("fhirUser");
        $scopeFactory = new ScopeValidatorFactory();
        $mockRestRequest = HttpRestRequest::create("/fhir/metadata");
        $mockRestRequest->setAccessTokenScopeValidationArray($scopeFactory->buildScopeValidatorArray(["fhirUser"]));

        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->once())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(false);
        $event->expects($this->atLeastOnce())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);
        $event->expects($this->once())
            ->method('getScopeType')
            ->willReturn('fhirUser');
        $event->expects($this->once())
            ->method('getResource')
            ->willReturn("");

        $result = $this->authListener->onRestApiSecurityCheck($event);

        $this->assertSame($event, $result);
    }

    /**
     * Test scope checking with resource and permission
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckValidatesScopeWithResource(): void
    {
        $mockScope = ScopeEntity::createFromString("fhirUser");
        $scopeFactory = new ScopeValidatorFactory();
        $mockRestRequest = HttpRestRequest::create("/fhir/Patient/123");
        $mockRestRequest->setAccessTokenScopeValidationArray($scopeFactory->buildScopeValidatorArray(["user/Patient.read"]));
        $event = $this->createMock(RestApiSecurityCheckEvent::class);
        $event->expects($this->once())
            ->method('shouldSkipSecurityCheck')
            ->willReturn(false);
        $event->expects($this->atLeastOnce())
            ->method('getRestRequest')
            ->willReturn($mockRestRequest);
        $event->expects($this->once())
            ->method('getScopeType')
            ->willReturn('user');
        $event->expects($this->atLeastOnce())
            ->method('getResource')
            ->willReturn('Patient');
        $event->expects($this->once())
            ->method('getPermission')
            ->willReturn('read');

        $result = $this->authListener->onRestApiSecurityCheck($event);

        $this->assertSame($event, $result);
    }

    /**
     * Test scope validation failure
     * @return void
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function testOnRestApiSecurityCheckDeniesInvalidScope(): void
    {
        $mockRestRequest = HttpRestRequest::create("/fhir/Patient/123");
        $event = new RestApiSecurityCheckEvent($mockRestRequest);
        $event->setScopeType('user');
        $event->setResource('Patient');
        $event->setPermission('read');
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('scope user/Patient.read not in access token');// invalid scope permission
        $result = $this->authListener->onRestApiSecurityCheck($event);

        $this->authListener->onRestApiSecurityCheck($event);
    }

    /**
     * Test that constraints from matching scope entities are added to request query parameters
     */
    public function testOnRestApiSecurityCheckUpdatesRequestWithConstraints(): void
    {
        $mockRestRequest = HttpRestRequest::create("/fhir/Patient/123");
        $mockRestRequest->query->set('existing', 'param');
        $scopeFactory = new ScopeValidatorFactory();
        $mockRestRequest->setAccessTokenScopeValidationArray($scopeFactory->buildScopeValidatorArray([
            "user/Patient.rs?patient=123&status=active"
            , "user/Observation.read"]));

        $this->mockLogger->expects($this->once())
            ->method('debug')
            ->with('Updated request with scope constraints', [
                'scope' => 'user/Patient.r',
                'constraints' => ['patient' => '123', 'status' => 'active'],
                'mergedQuery' => ['existing' => 'param', 'patient' => '123', 'status' => 'active']
            ]);
        $event = new RestApiSecurityCheckEvent($mockRestRequest);
        $event->setScopeType('user');
        $event->setResource('Patient');
        $event->setPermission('r');
        $result = $this->authListener->onRestApiSecurityCheck($event);
        $this->assertSame($event, $result);
        $this->assertEquals(
            ['existing' => 'param', 'patient' => '123', 'status' => 'active'],
            $mockRestRequest->getQueryParams(),
            'Query parameters should include merged constraints'
        );
    }

    /**
     * Test that constraints are merged when multiple scope entities match
     */
    public function testOnRestApiSecurityCheckMergesConstraintsFromMultipleScopes(): void
    {
        $mockRestRequest = HttpRestRequest::create("/fhir/Patient/123");
        $mockRestRequest->query->set('existing', 'param');
        $scopeFactory = new ScopeValidatorFactory();
        $mockRestRequest->setAccessTokenScopeValidationArray($scopeFactory->buildScopeValidatorArray([
            "user/Patient.rs?patient=123&status=active"
            ,"user/Patient.r?category=vital-signs&date=2023"]));

        $this->mockLogger->expects($this->once())
            ->method('debug')
            ->with('Updated request with scope constraints', [
                'scope' => 'user/Patient.r',
                'constraints' => ['patient' => '123', 'status' => 'active', 'category' => 'vital-signs', 'date' => '2023'],
                'mergedQuery' => ['existing' => 'param', 'patient' => '123', 'status' => 'active', 'category' => 'vital-signs', 'date' => '2023']
            ]);
        $event = new RestApiSecurityCheckEvent($mockRestRequest);
        $event->setScopeType('user');
        $event->setResource('Patient');
        $event->setPermission('r');
        $result = $this->authListener->onRestApiSecurityCheck($event);
        $this->assertSame($event, $result);
        $this->assertEquals(
            ['existing' => 'param', 'patient' => '123', 'status' => 'active', 'category' => 'vital-signs', 'date' => '2023'],
            $mockRestRequest->getQueryParams(),
            'Query parameters should include merged constraints'
        );
    }

    /**
     * Test that no constraints are applied when no scope entities match
     */
    public function testOnRestApiSecurityCheckNoConstraintsWhenScopeHasEmptyConstraints(): void
    {
        $mockRestRequest = HttpRestRequest::create("/fhir/Patient/123");
        $mockRestRequest->query->set('existing', 'param');
        $scopeFactory = new ScopeValidatorFactory();
        $mockRestRequest->setAccessTokenScopeValidationArray($scopeFactory->buildScopeValidatorArray([
            "user/Patient.rs"
            ,"user/Patient.r"]));

        $event = new RestApiSecurityCheckEvent($mockRestRequest);
        $event->setScopeType('user');
        $event->setResource('Patient');
        $event->setPermission('r');
        $result = $this->authListener->onRestApiSecurityCheck($event);
        $this->assertSame($event, $result);
        $this->assertEquals(
            ['existing' => 'param'],
            $mockRestRequest->getQueryParams(),
            'Query parameters should not have been modified'
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testClearAndAddAuthorizationStrategy(): void
    {
        $this->authListener->clearAuthorizationStrategies();
        $this->assertEmpty($this->authListener->getAuthorizationStrategies(), "Authorization strategies should be empty after clearing");
        $initialCount = count($this->authListener->getAuthorizationStrategies());

        $mockStrategy = $this->createMock(IAuthorizationStrategy::class);
        $this->authListener->addAuthorizationStrategy($mockStrategy);

        $strategies = $this->authListener->getAuthorizationStrategies();
        $this->assertCount($initialCount + 1, $strategies);
        $this->assertContains($mockStrategy, $strategies);
    }
}
