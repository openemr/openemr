<?php

/**
 * Isolated tests for the OIDC session-refresh handler.
 *
 * Every dependency is mocked — no database, HTTP, or session writes.
 * Covers all branches in {@see OidcSessionRefreshHandler::handle()}.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Session;

use OpenEMR\Common\Auth\Oidc\Audit\OidcRefreshAuditLoggerInterface;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryException;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcProviderMetadata;
use OpenEMR\Common\Auth\Oidc\Identity\NormalizedIdentity;
use OpenEMR\Common\Auth\Oidc\Session\OidcSessionRefreshHandler;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidationException;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\Oidc\Token\ValidatedToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class OidcSessionRefreshHandlerTest extends TestCase
{
    private const ISSUER = 'https://securetoken.google.com/my-project';
    private const AUDIENCE = 'my-client-id';
    private const JWKS_URI = 'https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com';
    private const SUBJECT = 'firebase-uid-abc';
    private const USERNAME = 'dr_smith';
    private const ID_TOKEN = 'header.payload.signature';

    private OidcTokenValidator&MockObject $tokenValidator;
    private OidcDiscoveryClient&MockObject $discoveryClient;
    private OidcRefreshAuditLoggerInterface&MockObject $auditLogger;

    private OidcSessionRefreshHandler $handler;

    protected function setUp(): void
    {
        $this->tokenValidator = $this->createMock(OidcTokenValidator::class);
        $this->discoveryClient = $this->createMock(OidcDiscoveryClient::class);
        $this->auditLogger = $this->createMock(OidcRefreshAuditLoggerInterface::class);

        $this->handler = new OidcSessionRefreshHandler(
            $this->tokenValidator,
            $this->discoveryClient,
            $this->auditLogger,
        );
    }

    // ---------------------------------------------------------------
    // Discovery failure
    // ---------------------------------------------------------------

    public function testDiscoveryFailureReturns401(): void
    {
        $this->discoveryClient->method('getMetadata')->willThrowException(new OidcDiscoveryException('down'));

        $this->auditLogger->expects(self::once())->method('discoveryFailed')->with(self::USERNAME);

        $result = $this->handler->handle(self::ID_TOKEN, self::ISSUER, self::AUDIENCE, self::SUBJECT, self::USERNAME);

        self::assertFalse($result->success);
        self::assertSame(401, $result->httpStatus);
        self::assertSame('token_invalid', $result->body['error']);
    }

    // ---------------------------------------------------------------
    // Token validation failure
    // ---------------------------------------------------------------

    public function testTokenValidationFailureReturns401(): void
    {
        $this->stubDiscovery();
        $this->tokenValidator->method('validate')->willThrowException(new OidcTokenValidationException('bad'));

        $this->auditLogger->expects(self::once())->method('tokenValidationFailed')->with(self::USERNAME);

        $result = $this->handler->handle(self::ID_TOKEN, self::ISSUER, self::AUDIENCE, self::SUBJECT, self::USERNAME);

        self::assertFalse($result->success);
        self::assertSame(401, $result->httpStatus);
        self::assertSame('token_invalid', $result->body['error']);
    }

    // ---------------------------------------------------------------
    // Issuer mismatch
    // ---------------------------------------------------------------

    public function testIssuerMismatchReturns401(): void
    {
        $this->stubDiscovery();
        $this->stubTokenValidation('https://evil-issuer.example.com');

        $this->auditLogger->expects(self::once())->method('issuerMismatch')->with(self::USERNAME);

        $result = $this->handler->handle(self::ID_TOKEN, self::ISSUER, self::AUDIENCE, self::SUBJECT, self::USERNAME);

        self::assertFalse($result->success);
        self::assertSame(401, $result->httpStatus);
        self::assertSame('issuer_mismatch', $result->body['error']);
    }

    // ---------------------------------------------------------------
    // Subject mismatch
    // ---------------------------------------------------------------

    public function testSubjectMismatchReturns401(): void
    {
        $this->stubDiscovery();
        $this->stubTokenValidation(self::ISSUER, 'different-subject');

        $this->auditLogger->expects(self::once())->method('subjectMismatch')->with(self::USERNAME);

        $result = $this->handler->handle(self::ID_TOKEN, self::ISSUER, self::AUDIENCE, self::SUBJECT, self::USERNAME);

        self::assertFalse($result->success);
        self::assertSame(401, $result->httpStatus);
        self::assertSame('subject_mismatch', $result->body['error']);
    }

    public function testSubjectPinningSkippedWhenSessionSubjectIsNull(): void
    {
        $this->stubDiscovery();
        $this->stubTokenValidation(self::ISSUER, 'any-subject');

        $this->auditLogger->expects(self::once())->method('refreshSucceeded');

        $result = $this->handler->handle(self::ID_TOKEN, self::ISSUER, self::AUDIENCE, null, self::USERNAME);

        self::assertTrue($result->success);
    }

    // ---------------------------------------------------------------
    // Success
    // ---------------------------------------------------------------

    public function testSuccessfulRefreshReturns200WithExpiresAt(): void
    {
        $this->stubDiscovery();
        $this->stubTokenValidation();

        $this->auditLogger->expects(self::once())->method('refreshSucceeded')->with(self::USERNAME);

        $result = $this->handler->handle(self::ID_TOKEN, self::ISSUER, self::AUDIENCE, self::SUBJECT, self::USERNAME);

        self::assertTrue($result->success);
        self::assertSame(200, $result->httpStatus);
        self::assertTrue($result->body['success']);
        self::assertArrayHasKey('expires_at', $result->body);
        self::assertIsInt($result->body['expires_at']);
        self::assertNotNull($result->validatedToken);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function stubDiscovery(): void
    {
        $metadata = new OidcProviderMetadata(
            issuer: self::ISSUER,
            jwksUri: self::JWKS_URI,
        );
        $this->discoveryClient->method('getMetadata')->willReturn($metadata);
    }

    private function stubTokenValidation(string $issuer = self::ISSUER, string $subject = self::SUBJECT): void
    {
        $identity = new NormalizedIdentity(
            externalId: $subject,
            issuer: $issuer,
        );
        $token = new ValidatedToken(
            identity: $identity,
            claims: ['sub' => $subject, 'iss' => $issuer],
            expiresAt: new \DateTimeImmutable('+1 hour'),
            jti: 'refresh-jti-1',
        );
        $this->tokenValidator->method('validate')->willReturn($token);
    }
}
