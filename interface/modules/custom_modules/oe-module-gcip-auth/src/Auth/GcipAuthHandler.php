<?php

/**
 * Handles OIDC authentication for the GCIP module.
 *
 * Listens for OidcLoginRequestEvent, validates the POSTed ID token via
 * the core OidcTokenValidator, resolves the local user via
 * ExternalIdentityRepository, and sets session metadata.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Modules\GcipAuth\Auth;

use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Event\OidcAuthenticationEvent;
use OpenEMR\Common\Auth\Oidc\Event\OidcLoginRequestEvent;
use OpenEMR\Common\Auth\Oidc\Identity\ExternalIdentityRepository;
use OpenEMR\Common\Auth\Oidc\Session\OidcSessionHelper;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidationException;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\Oidc\Token\OidcValidationParameters;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Modules\GcipAuth\Config\GcipConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class GcipAuthHandler
{
    public function __construct(
        private OidcTokenValidator $tokenValidator,
        private OidcDiscoveryClient $discoveryClient,
        private ExternalIdentityRepository $identityRepository,
        private GcipConfigService $configService,
        private EventDispatcherInterface $eventDispatcher,
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
            EventAuditLogger::getInstance()->newEvent(
                'login',
                '',
                '',
                0,
                'GCIP module not configured (missing issuer or client ID)',
            );
            return;
        }

        // Resolve JWKS URI from discovery
        try {
            $metadata = $this->discoveryClient->getMetadata($issuer);
        } catch (\Throwable) {
            EventAuditLogger::getInstance()->newEvent(
                'login',
                '',
                '',
                0,
                'GCIP OIDC discovery failed',
            );
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
            EventAuditLogger::getInstance()->newEvent(
                'login',
                '',
                '',
                0,
                'GCIP OIDC token validation failed',
            );
            return;
        }

        // Look up local user by external identity
        $mapping = $this->identityRepository->findByExternal(
            $validatedToken->identity->issuer,
            $validatedToken->identity->externalId,
        );

        if ($mapping === null) {
            EventAuditLogger::getInstance()->newEvent(
                'login',
                '',
                '',
                0,
                'GCIP OIDC account not provisioned for iss=' . $validatedToken->identity->issuer
                    . ' sub=' . $validatedToken->identity->externalId,
            );
            return;
        }

        // Fetch local user record
        $userRow = $this->fetchUserById($mapping->userId);
        if ($userRow === null) {
            EventAuditLogger::getInstance()->newEvent(
                'login',
                '',
                '',
                0,
                'GCIP OIDC mapped user not found in users table',
            );
            return;
        }

        if (!isset($userRow['active']) || $userRow['active'] === 0 || $userRow['active'] === '0' || $userRow['active'] === '') {
            $disabledUsername = is_string($userRow['username'] ?? null) ? $userRow['username'] : '';
            EventAuditLogger::getInstance()->newEvent(
                'login',
                $disabledUsername,
                '',
                0,
                'GCIP OIDC user account is disabled',
            );
            return;
        }

        $username = is_string($userRow['username'] ?? null) ? $userRow['username'] : '';

        // Resolve auth group (gACL stores username, not user ID)
        $authGroup = $this->resolveAuthGroup($username);
        if ($authGroup === '') {
            EventAuditLogger::getInstance()->newEvent(
                'login',
                $username,
                '',
                0,
                'GCIP OIDC user has no ACL group',
            );
            return;
        }

        // Get password hash for session verification
        $passwordHash = $this->fetchPasswordHash($username);

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

        EventAuditLogger::getInstance()->newEvent(
            'login',
            $username,
            $authGroup,
            1,
            'success via GCIP OIDC',
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchUserById(int $userId): ?array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT * FROM `users` WHERE `id` = ?',
            [$userId],
        );

        if ($rows === []) {
            return null;
        }

        /** @var array<string, mixed> $row */
        $row = $rows[0];
        return $row;
    }

    private function resolveAuthGroup(string $username): string
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `gacl_aro_groups`.`value` FROM `gacl_aro` '
            . 'INNER JOIN `gacl_groups_aro_map` ON `gacl_groups_aro_map`.`aro_id` = `gacl_aro`.`id` '
            . 'INNER JOIN `gacl_aro_groups` ON `gacl_aro_groups`.`id` = `gacl_groups_aro_map`.`group_id` '
            . 'WHERE `gacl_aro`.`section_value` = ? AND `gacl_aro`.`value` = ?',
            ['users', $username],
        );

        if ($rows === []) {
            return '';
        }

        $value = $rows[0]['value'] ?? '';
        return is_string($value) ? $value : '';
    }

    private function fetchPasswordHash(string $username): string
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `password` FROM `users_secure` WHERE `username` = ?',
            [$username],
        );

        if ($rows === []) {
            return '';
        }

        $password = $rows[0]['password'] ?? '';
        return is_string($password) ? $password : '';
    }
}
