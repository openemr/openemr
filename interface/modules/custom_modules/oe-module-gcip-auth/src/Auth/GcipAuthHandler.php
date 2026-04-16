<?php

/**
 * Handles OIDC authentication for the GCIP module.
 *
 * Listens for OidcLoginRequestEvent, validates the POSTed ID token via
 * the core OidcTokenValidator, resolves the local user via
 * ExternalIdentityRepository + LocalUserDirectoryInterface, and sets
 * session metadata.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Modules\GcipAuth\Auth;

use OpenEMR\Common\Auth\Oidc\Audit\OidcLoginAuditLoggerInterface;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryException;
use OpenEMR\Common\Auth\Oidc\Event\OidcAuthenticationEvent;
use OpenEMR\Common\Auth\Oidc\Event\OidcLoginRequestEvent;
use OpenEMR\Common\Auth\Oidc\Identity\ExternalIdentityRepository;
use OpenEMR\Common\Auth\Oidc\Identity\LocalUserDirectoryInterface;
use OpenEMR\Common\Auth\Oidc\Session\OidcSessionHelper;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidationException;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\Oidc\Token\OidcValidationParameters;
use OpenEMR\Modules\GcipAuth\Config\GcipConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class GcipAuthHandler
{
    public function __construct(
        private OidcTokenValidator $tokenValidator,
        private OidcDiscoveryClient $discoveryClient,
        private ExternalIdentityRepository $identityRepository,
        private LocalUserDirectoryInterface $localUserDirectory,
        private GcipConfigService $configService,
        private EventDispatcherInterface $eventDispatcher,
        private OidcLoginAuditLoggerInterface $auditLogger,
    ) {
    }

    public function onLoginRequest(OidcLoginRequestEvent $event): void
    {
        $idToken = $event->getPostParam('oidc_id_token');
        if ($idToken === null || $idToken === '') {
            return; // Not an OIDC login attempt — let other auth paths handle it
        }

        $issuer = $this->configService->getIssuer();
        $clientId = $this->configService->getClientId();

        if ($issuer === '' || $clientId === '') {
            $this->auditLogger->moduleNotConfigured();
            return;
        }

        // Resolve JWKS URI from discovery
        try {
            $metadata = $this->discoveryClient->getMetadata($issuer);
        } catch (OidcDiscoveryException) {
            $this->auditLogger->discoveryFailed();
            return;
        }

        // Validate the ID token
        $parameters = new OidcValidationParameters(
            expectedIssuer: $issuer,
            expectedAudience: $clientId,
        );

        try {
            $validatedToken = $this->tokenValidator->validate($idToken, $metadata->jwksUri, $parameters);
        } catch (OidcTokenValidationException) {
            $this->auditLogger->tokenValidationFailed();
            return;
        }

        // Look up local user by external identity
        $mapping = $this->identityRepository->findByExternal(
            $validatedToken->identity->issuer,
            $validatedToken->identity->externalId,
        );

        if ($mapping === null) {
            $this->auditLogger->accountNotProvisioned(
                $validatedToken->identity->issuer,
                $validatedToken->identity->externalId,
            );
            return;
        }

        // Fetch local user record
        $userRow = $this->localUserDirectory->findUserById($mapping->userId);
        if ($userRow === null) {
            $this->auditLogger->mappedUserMissing();
            return;
        }

        if (!isset($userRow['active']) || $userRow['active'] === 0 || $userRow['active'] === '0' || $userRow['active'] === '') {
            $disabledUsername = is_string($userRow['username'] ?? null) ? $userRow['username'] : '';
            $this->auditLogger->userAccountDisabled($disabledUsername);
            return;
        }

        $username = is_string($userRow['username'] ?? null) ? $userRow['username'] : '';

        // Resolve auth group (gACL stores username, not user ID)
        $authGroup = $this->localUserDirectory->findAuthGroupFor($username);
        if ($authGroup === '') {
            $this->auditLogger->userHasNoAuthGroup($username);
            return;
        }

        // Get password hash for session verification
        $passwordHash = $this->localUserDirectory->findPasswordHashFor($username);

        // Set authenticated user on the event
        $event->setAuthenticatedUser(
            $username,
            $passwordHash,
            $userRow,
            $authGroup,
        );

        // Store OIDC session metadata
        OidcSessionHelper::setTokenMetadata(
            $validatedToken->expiresAt,
            $validatedToken->identity->issuer,
            $validatedToken->jti,
            $validatedToken->identity->externalId,
            $clientId,
        );

        // Dispatch post-authentication event
        $authEvent = new OidcAuthenticationEvent(
            identity: $validatedToken->identity,
            userId: $mapping->userId,
            username: $username,
            expiresAt: $validatedToken->expiresAt,
            jti: $validatedToken->jti,
            claims: $validatedToken->claims,
        );
        $this->eventDispatcher->dispatch($authEvent, OidcAuthenticationEvent::EVENT_NAME);

        $this->auditLogger->loginSucceeded($username, $authGroup);
    }
}
