<?php

/**
 * Isolated tests for the GCIP OIDC login handler.
 *
 * Every dependency is mocked — no database, no HTTP, no session writes.
 * Covers all 9 branches in `onLoginRequest()`.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\GcipAuth\Auth;

use OpenEMR\Common\Auth\Oidc\Audit\OidcLoginAuditLoggerInterface;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryException;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcProviderMetadata;
use OpenEMR\Common\Auth\Oidc\Event\OidcAuthenticationEvent;
use OpenEMR\Common\Auth\Oidc\Event\OidcLoginRequestEvent;
use OpenEMR\Common\Auth\Oidc\Identity\ExternalIdentityMapping;
use OpenEMR\Common\Auth\Oidc\Identity\ExternalIdentityRepository;
use OpenEMR\Common\Auth\Oidc\Identity\LocalUserDirectoryInterface;
use OpenEMR\Common\Auth\Oidc\Identity\NormalizedIdentity;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidationException;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\Oidc\Token\ValidatedToken;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Modules\GcipAuth\Auth\GcipAuthHandler;
use OpenEMR\Modules\GcipAuth\Config\GcipConfigService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class GcipAuthHandlerTest extends TestCase
{
    private const ISSUER = 'https://securetoken.google.com/my-project';
    private const CLIENT_ID = 'my-client-id';
    private const JWKS_URI = 'https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com';
    private const EXTERNAL_ID = 'firebase-uid-abc';
    private const USERNAME = 'dr_smith';
    private const AUTH_GROUP = 'Physicians';
    private const PASSWORD_HASH = '$2y$10$fakehash';
    private const USER_ID = 42;

    private OidcTokenValidator&MockObject $tokenValidator;
    private OidcDiscoveryClient&MockObject $discoveryClient;
    private ExternalIdentityRepository&MockObject $identityRepository;
    private LocalUserDirectoryInterface&MockObject $localUserDirectory;
    private GcipConfigService&MockObject $configService;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private OidcLoginAuditLoggerInterface&MockObject $auditLogger;

    private GcipAuthHandler $handler;

    protected function setUp(): void
    {
        $this->tokenValidator = $this->createMock(OidcTokenValidator::class);
        $this->discoveryClient = $this->createMock(OidcDiscoveryClient::class);
        $this->identityRepository = $this->createMock(ExternalIdentityRepository::class);
        $this->localUserDirectory = $this->createMock(LocalUserDirectoryInterface::class);
        $this->configService = $this->createMock(GcipConfigService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->auditLogger = $this->createMock(OidcLoginAuditLoggerInterface::class);

        $this->handler = new GcipAuthHandler(
            $this->tokenValidator,
            $this->discoveryClient,
            $this->identityRepository,
            $this->localUserDirectory,
            $this->configService,
            $this->eventDispatcher,
            $this->auditLogger,
        );

        SessionWrapperFactory::getInstance()->setActiveSession($this->createSessionStub());
    }

    // ---------------------------------------------------------------
    // Branch 1: no oidc_id_token → silent pass-through
    // ---------------------------------------------------------------

    public function testSilentReturnWhenNoOidcTokenPosted(): void
    {
        $event = new OidcLoginRequestEvent([]);

        $this->auditLogger->expects(self::never())->method(self::anything());

        $this->handler->onLoginRequest($event);

        self::assertFalse($event->isHandled());
    }

    public function testSilentReturnWhenOidcTokenIsEmpty(): void
    {
        $event = new OidcLoginRequestEvent(['oidc_id_token' => '']);

        $this->auditLogger->expects(self::never())->method(self::anything());

        $this->handler->onLoginRequest($event);

        self::assertFalse($event->isHandled());
    }

    // ---------------------------------------------------------------
    // Branch 2: module not configured
    // ---------------------------------------------------------------

    public function testModuleNotConfiguredLogsAndReturns(): void
    {
        $this->stubConfig('', self::CLIENT_ID);

        $this->auditLogger->expects(self::once())->method('moduleNotConfigured');

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);

        self::assertFalse($event->isHandled());
    }

    // ---------------------------------------------------------------
    // Branch 3: discovery failure
    // ---------------------------------------------------------------

    public function testDiscoveryFailureLogsAndReturns(): void
    {
        $this->stubConfig();
        $this->discoveryClient->method('getMetadata')->willThrowException(new OidcDiscoveryException('down'));

        $this->auditLogger->expects(self::once())->method('discoveryFailed');

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);

        self::assertFalse($event->isHandled());
    }

    // ---------------------------------------------------------------
    // Branch 4: token validation failure
    // ---------------------------------------------------------------

    public function testTokenValidationFailureLogsAndReturns(): void
    {
        $this->stubConfig();
        $this->stubDiscovery();
        $this->tokenValidator->method('validate')->willThrowException(new OidcTokenValidationException('bad'));

        $this->auditLogger->expects(self::once())->method('tokenValidationFailed');

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);

        self::assertFalse($event->isHandled());
    }

    // ---------------------------------------------------------------
    // Branch 5: unknown external identity
    // ---------------------------------------------------------------

    public function testUnknownExternalIdentityLogsAndReturns(): void
    {
        $this->stubConfig();
        $this->stubDiscovery();
        $this->stubTokenValidation();
        $this->identityRepository->method('findByExternal')->willReturn(null);

        $this->auditLogger->expects(self::once())
            ->method('accountNotProvisioned')
            ->with(self::ISSUER, self::EXTERNAL_ID);

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);

        self::assertFalse($event->isHandled());
    }

    // ---------------------------------------------------------------
    // Branch 6: mapped user row missing in users table
    // ---------------------------------------------------------------

    public function testMappedUserMissingInDatabaseLogsAndReturns(): void
    {
        $this->stubConfig();
        $this->stubDiscovery();
        $this->stubTokenValidation();
        $this->stubIdentityMapping();
        $this->localUserDirectory->method('findUserById')->willReturn(null);

        $this->auditLogger->expects(self::once())->method('mappedUserMissing');

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);

        self::assertFalse($event->isHandled());
    }

    // ---------------------------------------------------------------
    // Branch 7: user account disabled
    // ---------------------------------------------------------------

    public function testDisabledUserAccountLogsAndReturns(): void
    {
        $this->stubConfig();
        $this->stubDiscovery();
        $this->stubTokenValidation();
        $this->stubIdentityMapping();
        $this->localUserDirectory->method('findUserById')->willReturn(
            ['id' => self::USER_ID, 'username' => self::USERNAME, 'active' => 0],
        );

        $this->auditLogger->expects(self::once())
            ->method('userAccountDisabled')
            ->with(self::USERNAME);

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);

        self::assertFalse($event->isHandled());
    }

    // ---------------------------------------------------------------
    // Branch 8: no ACL group
    // ---------------------------------------------------------------

    public function testUserWithNoAclGroupLogsAndReturns(): void
    {
        $this->stubConfig();
        $this->stubDiscovery();
        $this->stubTokenValidation();
        $this->stubIdentityMapping();
        $this->stubActiveUserRow();
        $this->localUserDirectory->method('findAuthGroupFor')->willReturn('');

        $this->auditLogger->expects(self::once())
            ->method('userHasNoAuthGroup')
            ->with(self::USERNAME);

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);

        self::assertFalse($event->isHandled());
    }

    // ---------------------------------------------------------------
    // Branch 9: full success
    // ---------------------------------------------------------------

    public function testSuccessfulLoginSetsAuthenticatedUser(): void
    {
        $this->stubFullHappyPath();

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);

        self::assertTrue($event->isHandled());
        self::assertSame(self::USERNAME, $event->getUsername());
        self::assertSame(self::AUTH_GROUP, $event->getAuthGroup());
        self::assertSame(self::PASSWORD_HASH, $event->getPasswordHash());
    }

    public function testSuccessfulLoginDispatchesAuthenticationEvent(): void
    {
        $this->stubFullHappyPath();

        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(
                self::isInstanceOf(OidcAuthenticationEvent::class),
                OidcAuthenticationEvent::EVENT_NAME,
            );

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);
    }

    public function testSuccessfulLoginAuditsSuccess(): void
    {
        $this->stubFullHappyPath();

        $this->auditLogger->expects(self::once())
            ->method('loginSucceeded')
            ->with(self::USERNAME, self::AUTH_GROUP);

        $event = $this->makeEvent();
        $this->handler->onLoginRequest($event);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function makeEvent(): OidcLoginRequestEvent
    {
        return new OidcLoginRequestEvent(['oidc_id_token' => 'header.payload.signature']);
    }

    private function stubConfig(string $issuer = self::ISSUER, string $clientId = self::CLIENT_ID): void
    {
        $this->configService->method('getIssuer')->willReturn($issuer);
        $this->configService->method('getClientId')->willReturn($clientId);
    }

    private function stubDiscovery(): void
    {
        $metadata = new OidcProviderMetadata(
            issuer: self::ISSUER,
            jwksUri: self::JWKS_URI,
        );
        $this->discoveryClient->method('getMetadata')->willReturn($metadata);
    }

    private function stubTokenValidation(): void
    {
        $identity = new NormalizedIdentity(
            externalId: self::EXTERNAL_ID,
            issuer: self::ISSUER,
            email: 'dr.smith@example.com',
            emailVerified: true,
            displayName: 'Dr. Smith',
        );
        $token = new ValidatedToken(
            identity: $identity,
            claims: ['sub' => self::EXTERNAL_ID, 'iss' => self::ISSUER],
            expiresAt: new \DateTimeImmutable('+1 hour'),
            jti: 'test-jti-1',
        );
        $this->tokenValidator->method('validate')->willReturn($token);
    }

    private function stubIdentityMapping(): void
    {
        $mapping = new ExternalIdentityMapping(
            userId: self::USER_ID,
            issuer: self::ISSUER,
            externalId: self::EXTERNAL_ID,
        );
        $this->identityRepository->method('findByExternal')->willReturn($mapping);
    }

    private function stubActiveUserRow(): void
    {
        $this->localUserDirectory->method('findUserById')->willReturn(
            ['id' => self::USER_ID, 'username' => self::USERNAME, 'active' => 1],
        );
    }

    private function stubFullHappyPath(): void
    {
        $this->stubConfig();
        $this->stubDiscovery();
        $this->stubTokenValidation();
        $this->stubIdentityMapping();
        $this->stubActiveUserRow();
        $this->localUserDirectory->method('findAuthGroupFor')->willReturn(self::AUTH_GROUP);
        $this->localUserDirectory->method('findPasswordHashFor')->willReturn(self::PASSWORD_HASH);
    }

    private function createSessionStub(): SessionInterface
    {
        $store = [];
        $session = $this->createStub(SessionInterface::class);
        $session->method('set')
            ->willReturnCallback(function (string $key, mixed $value) use (&$store): void {
                $store[$key] = $value;
            });
        $session->method('get')
            ->willReturnCallback(function (string $key, mixed $default = null) use (&$store): mixed {
                return $store[$key] ?? $default;
            });
        return $session;
    }
}
