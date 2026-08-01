<?php

/**
 * Resolves or provisions local OpenEMR users for External IdP logins.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ExternalIdp\Service;

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Modules\ExternalIdp\Repository\IdentityRepository;
use OpenEMR\Modules\ExternalIdp\Repository\ProviderRepository;

final class OidcProvisioningService
{
    public function __construct(
        private readonly IdentityRepository $identityRepository = new IdentityRepository(),
        private readonly ProviderRepository $providerRepository = new ProviderRepository(),
    ) {
    }

    /**
     * @param array<string, mixed> $provider
     */
    public function resolveOrProvisionUser(array $provider, object $claims): ?int
    {
        $providerId = (int) ($provider['id'] ?? 0);
        $subject = $this->requireSubject($claims);
        $mode = (string) ($provider['provisioning_mode'] ?? ProviderRepository::DEFAULT_PROVISIONING_MODE);

        if ($mode === 'auto_bind' || $mode === 'auto_bind_or_provision') {
            $matchedUserId = $this->tryAutoBindExistingUser($provider, $claims, $subject);
            if ($matchedUserId !== null) {
                return $matchedUserId;
            }
        }

        if ($mode === 'auto_provision' || $mode === 'auto_bind_or_provision') {
            $userId = $this->provisionShadowUser($provider, $claims, $subject);
            EventAuditLogger::getInstance()->newEvent('external_login_provisioned', '', (string) $providerId, 1, 'provisioned local shadow user #' . $userId);
            return $userId;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $provider
     */
    public function syncMappedUser(array $provider, int $userId, object $claims): void
    {
        if ((int) ($provider['sync_claims_on_login'] ?? 1) !== 1 || $userId < 1) {
            return;
        }

        $updates = [];
        $params = [];
        $firstName = $this->claimString($claims, (string) ($provider['first_name_claim'] ?? 'given_name'));
        $lastName = $this->claimString($claims, (string) ($provider['last_name_claim'] ?? 'family_name'));
        $email = $this->claimString($claims, (string) ($provider['email_claim'] ?? 'email'));

        if ($firstName !== '') {
            $updates[] = '`fname` = ?';
            $params[] = $firstName;
        }
        if ($lastName !== '') {
            $updates[] = '`lname` = ?';
            $params[] = $lastName;
        }
        if ($email !== '') {
            $updates[] = '`email` = ?';
            $params[] = $email;
        }
        if ($updates === []) {
            return;
        }

        $params[] = $userId;
        sqlStatement('UPDATE `users` SET ' . implode(', ', $updates) . ' WHERE `id` = ?', $params);
    }

    /**
     * @param array<string, mixed> $provider
     */
    private function tryAutoBindExistingUser(array $provider, object $claims, string $subject): ?int
    {
        $claimName = trim((string) ($provider['match_claim'] ?? 'preferred_username'));
        $claimValue = $this->claimString($claims, $claimName);
        if ($claimValue === '') {
            throw new \RuntimeException('The configured match claim is missing from the identity token.');
        }

        $userField = $claimName === 'email' ? 'email' : 'username';
        $matches = $this->providerRepository->findActiveUsersByClaim($userField, $claimValue, 2);
        if ($matches === []) {
            return null;
        }
        if (count($matches) > 1) {
            throw new \RuntimeException('Multiple active OpenEMR users matched the configured auto-bind claim.');
        }

        $userId = (int) ($matches[0]['id'] ?? 0);
        if ($userId < 1) {
            return null;
        }
        $this->identityRepository->saveBinding((int) $provider['id'], $subject, $userId);
        EventAuditLogger::getInstance()->newEvent('external_login_auto_bind', '', (string) $provider['id'], 1, 'auto-bound local user #' . $userId);
        return $userId;
    }

    /**
     * @param array<string, mixed> $provider
     */
    private function provisionShadowUser(array $provider, object $claims, string $subject): int
    {
        $groupName = trim((string) ($provider['default_group_name'] ?? ''));
        $aclGroup = trim((string) ($provider['default_acl_group'] ?? ''));
        if ($groupName === '' || $aclGroup === '') {
            throw new \RuntimeException('Shadow-user provisioning requires both a local group name and an ACL group.');
        }

        $firstName = $this->claimString($claims, (string) ($provider['first_name_claim'] ?? 'given_name'));
        $lastName = $this->claimString($claims, (string) ($provider['last_name_claim'] ?? 'family_name'));
        $email = $this->claimString($claims, (string) ($provider['email_claim'] ?? 'email'));
        $usernameSource = $this->claimString($claims, (string) ($provider['username_claim'] ?? 'preferred_username'));
        $username = $this->buildUniqueUsername((string) ($provider['username_prefix'] ?? 'oidc_'), $usernameSource, (int) $provider['id'], $subject);
        $facilityId = max(0, (int) ($provider['default_facility_id'] ?? 0));
        $authorized = !empty($provider['default_authorized']) ? 1 : 0;
        $active = !empty($provider['default_active']) ? 1 : 0;
        $passwordHash = (new AuthHash())->passwordHash(OidcStateService::generateToken(48));
        if ($passwordHash === '') {
            throw new \RuntimeException('Unable to generate a local password hash for the shadow user.');
        }

        $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();
        $userId = sqlInsert(
            'INSERT INTO `users`
                (`username`, `password`, `authorized`, `fname`, `lname`, `email`, `active`, `see_auth`, `calendar`, `portal_user`, `facility_id`, `billing_facility_id`, `uuid`)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 0, ?, ?, ?)',
            [$username, 'NoLongerUsed', $authorized, $firstName, $lastName, $email !== '' ? $email : null, $active, $facilityId, $facilityId, $uuid]
        );
        if (!is_numeric($userId) || (int) $userId < 1) {
            throw new \RuntimeException('Unable to create the local OpenEMR user.');
        }
        $userId = (int) $userId;

        privStatement(
            'INSERT INTO `users_secure` (`id`, `username`, `password`, `last_update_password`) VALUES (?, ?, ?, NOW())',
            [$userId, $username, $passwordHash]
        );

        if ($facilityId > 0) {
            sqlStatement(
                'UPDATE `users` u
                 INNER JOIN `facility` f ON f.`id` = ?
                 SET u.`facility` = f.`name`, u.`billing_facility` = f.`name`
                 WHERE u.`id` = ?',
                [$facilityId, $userId]
            );
        }

        sqlStatement('INSERT INTO `groups` SET `name` = ?, `user` = ?', [$groupName, $username]);
        AclExtended::setUserAro([$aclGroup], $username, $firstName, '', $lastName);

        $this->identityRepository->saveBinding((int) $provider['id'], $subject, $userId);
        return $userId;
    }

    private function requireSubject(object $claims): string
    {
        $subject = isset($claims->sub) && is_string($claims->sub) ? trim($claims->sub) : '';
        if ($subject === '') {
            throw new \RuntimeException('OIDC subject claim is missing.');
        }
        return $subject;
    }

    private function claimString(object $claims, string $claimName): string
    {
        $claimName = trim($claimName);
        if ($claimName === '') {
            return '';
        }

        $value = $claims->{$claimName} ?? null;
        if (is_string($value)) {
            return trim($value);
        }
        if (is_numeric($value)) {
            return trim((string) $value);
        }
        return '';
    }

    private function buildUniqueUsername(string $prefix, string $source, int $providerId, string $subject): string
    {
        $prefix = preg_replace('/[^A-Za-z0-9_.-]/', '', $prefix) ?: 'oidc_';
        $normalized = strtolower(trim($source));
        $normalized = preg_replace('/@.*/', '', $normalized) ?? '';
        $normalized = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $normalized) ?? '';
        $normalized = trim($normalized, '._-');
        if ($normalized === '') {
            $normalized = 'user';
        }

        $candidate = substr($prefix . $normalized, 0, 255);
        if (!$this->usernameExists($candidate)) {
            return $candidate;
        }

        $suffix = substr(hash('sha256', $providerId . ':' . $subject), 0, 8);
        $base = substr($prefix . $normalized, 0, max(1, 255 - strlen($suffix) - 1));
        $candidate = $base . '_' . $suffix;
        if (!$this->usernameExists($candidate)) {
            return $candidate;
        }

        for ($i = 1; $i < 1000; $i++) {
            $withCounter = substr($base, 0, max(1, 255 - strlen((string) $i) - 1)) . '_' . $i;
            if (!$this->usernameExists($withCounter)) {
                return $withCounter;
            }
        }

        throw new \RuntimeException('Unable to generate a unique local OpenEMR username for the external user.');
    }

    private function usernameExists(string $username): bool
    {
        $row = sqlQuery('SELECT `id` FROM `users` WHERE BINARY `username` = ? LIMIT 1', [$username]);
        return !empty($row['id']);
    }
}
