<?php

/**
 * Persists external OIDC provider configuration.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ExternalIdp\Repository;

use OpenEMR\BC\ServiceContainer;

final class ProviderRepository
{
    public const PROVISIONING_MODES = ['manual', 'auto_bind', 'auto_provision', 'auto_bind_or_provision'];
    public const DEFAULT_PROVISIONING_MODE = 'manual';

    /** @return array<string, mixed>|false */
    public function getForSite(string $siteId): array|false
    {
        return $this->hydrateProvider(sqlQuery('SELECT * FROM `module_external_idp_provider` WHERE `site_id` = ?', [$siteId]));
    }

    /** @return array<string, mixed>|false */
    public function getEnabledForSite(string $siteId): array|false
    {
        return $this->hydrateProvider(sqlQuery('SELECT * FROM `module_external_idp_provider` WHERE `site_id` = ? AND `enabled` = 1', [$siteId]));
    }

    /** @return array<string, mixed>|false */
    public function getById(int $id): array|false
    {
        return $this->hydrateProvider(sqlQuery('SELECT * FROM `module_external_idp_provider` WHERE `id` = ?', [$id]));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchUsers(string $search, int $limit = 25): array
    {
        $search = trim($search);
        if ($search === '') {
            return [];
        }

        $like = '%' . $search . '%';
        $rows = sqlStatement(
            'SELECT `id`, `username`, `fname`, `lname`, `active`, `authorized`, `see_auth`
             FROM `users`
             WHERE `active` = 1
               AND (`username` LIKE ?
                OR `fname` LIKE ?
                OR `lname` LIKE ?
                OR CONCAT(`fname`, " ", `lname`) LIKE ?)
             ORDER BY `lname`, `fname`
             LIMIT ' . (int) $limit,
            [$like, $like, $like, $like]
        );

        $users = [];
        while ($row = sqlFetchArray($rows)) {
            $users[] = $row;
        }
        return $users;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findActiveUsersByClaim(string $field, string $value, int $limit = 2): array
    {
        $field = $field === 'email' ? 'email' : 'username';
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $rows = sqlStatement(
            'SELECT `id`, `username`, `fname`, `lname`, `email`, `active`, `authorized`
             FROM `users`
             WHERE `active` = 1 AND BINARY `' . $field . '` = ?
             ORDER BY `id`
             LIMIT ' . (int) $limit,
            [$value]
        );

        $users = [];
        while ($row = sqlFetchArray($rows)) {
            $users[] = $row;
        }
        return $users;
    }

    public function removeBinding(int $bindingId): void
    {
        sqlStatement('DELETE FROM `module_external_idp_identity` WHERE `id` = ?', [$bindingId]);
    }

    public function markStart(int $providerId): void
    {
        sqlStatement('UPDATE `module_external_idp_provider` SET `last_started_at` = NOW() WHERE `id` = ?', [$providerId]);
    }

    public function markSuccess(int $providerId, int $userId): void
    {
        sqlStatement(
            'UPDATE `module_external_idp_provider`
             SET `last_success_at` = NOW(),
                 `last_success_user_id` = ?,
                 `last_failure_at` = NULL,
                 `last_failure_message` = NULL
             WHERE `id` = ?',
            [$userId, $providerId]
        );
    }

    public function markFailure(int $providerId, string $message): void
    {
        sqlStatement(
            'UPDATE `module_external_idp_provider`
             SET `last_failure_at` = NOW(),
                 `last_failure_message` = ?
             WHERE `id` = ?',
            [$message, $providerId]
        );
    }

    public function setEnabled(int $providerId, bool $enabled): void
    {
        sqlStatement('UPDATE `module_external_idp_provider` SET `enabled` = ? WHERE `id` = ?', [$enabled ? 1 : 0, $providerId]);
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function save(string $siteId, string $displayName, string $issuerUrl, string $clientId, string $clientSecret, string $scopes, bool $enabled, array $metadata, array $settings = []): void
    {
        $encryptedSecret = $clientSecret === '' ? null : ServiceContainer::getCrypto()->encryptForDatabase($clientSecret);
        $metadataJson = json_encode($metadata, JSON_THROW_ON_ERROR);
        $provisioningMode = (string) ($settings['provisioning_mode'] ?? self::DEFAULT_PROVISIONING_MODE);
        if (!in_array($provisioningMode, self::PROVISIONING_MODES, true)) {
            $provisioningMode = self::DEFAULT_PROVISIONING_MODE;
        }
        $matchClaim = trim((string) ($settings['match_claim'] ?? 'preferred_username')) ?: 'preferred_username';
        $usernameClaim = trim((string) ($settings['username_claim'] ?? 'preferred_username')) ?: 'preferred_username';
        $emailClaim = trim((string) ($settings['email_claim'] ?? 'email')) ?: 'email';
        $firstNameClaim = trim((string) ($settings['first_name_claim'] ?? 'given_name')) ?: 'given_name';
        $lastNameClaim = trim((string) ($settings['last_name_claim'] ?? 'family_name')) ?: 'family_name';
        $defaultGroupName = trim((string) ($settings['default_group_name'] ?? ''));
        $defaultAclGroup = trim((string) ($settings['default_acl_group'] ?? ''));
        $usernamePrefix = substr(trim((string) ($settings['username_prefix'] ?? 'oidc_')) ?: 'oidc_', 0, 32);
        $defaultFacilityId = max(0, (int) ($settings['default_facility_id'] ?? 0));
        $defaultAuthorized = !empty($settings['default_authorized']) ? 1 : 0;
        $defaultActive = !empty($settings['default_active']) ? 1 : 0;
        $syncClaimsOnLogin = !array_key_exists('sync_claims_on_login', $settings) || !empty($settings['sync_claims_on_login']) ? 1 : 0;
        sqlStatement(
            'INSERT INTO `module_external_idp_provider`
                (`site_id`, `display_name`, `issuer_url`, `client_id`, `client_secret`, `scopes`,
                 `provisioning_mode`, `match_claim`, `username_claim`, `email_claim`, `first_name_claim`, `last_name_claim`,
                 `default_group_name`, `default_acl_group`, `username_prefix`, `default_facility_id`, `default_authorized`, `default_active`, `sync_claims_on_login`,
                 `discovery_document`, `discovery_fetched_at`, `enabled`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
             ON DUPLICATE KEY UPDATE
                `display_name` = VALUES(`display_name`),
                `issuer_url` = VALUES(`issuer_url`),
                `client_id` = VALUES(`client_id`),
                `client_secret` = COALESCE(VALUES(`client_secret`), `client_secret`),
                `scopes` = VALUES(`scopes`),
                `provisioning_mode` = VALUES(`provisioning_mode`),
                `match_claim` = VALUES(`match_claim`),
                `username_claim` = VALUES(`username_claim`),
                `email_claim` = VALUES(`email_claim`),
                `first_name_claim` = VALUES(`first_name_claim`),
                `last_name_claim` = VALUES(`last_name_claim`),
                `default_group_name` = VALUES(`default_group_name`),
                `default_acl_group` = VALUES(`default_acl_group`),
                `username_prefix` = VALUES(`username_prefix`),
                `default_facility_id` = VALUES(`default_facility_id`),
                `default_authorized` = VALUES(`default_authorized`),
                `default_active` = VALUES(`default_active`),
                `sync_claims_on_login` = VALUES(`sync_claims_on_login`),
                `discovery_document` = VALUES(`discovery_document`),
                `discovery_fetched_at` = NOW(),
                `enabled` = VALUES(`enabled`)',
            [
                $siteId, $displayName, $issuerUrl, $clientId, $encryptedSecret, $scopes,
                $provisioningMode, $matchClaim, $usernameClaim, $emailClaim, $firstNameClaim, $lastNameClaim,
                $defaultGroupName, $defaultAclGroup, $usernamePrefix, $defaultFacilityId, $defaultAuthorized, $defaultActive, $syncClaimsOnLogin,
                $metadataJson, $enabled ? 1 : 0
            ]
        );
    }

    /** @param array<string, mixed>|false $provider */
    private function hydrateProvider(array|false $provider): array|false
    {
        if (!is_array($provider)) {
            return $provider;
        }

        $encryptedSecret = $provider['client_secret'] ?? null;
        if (is_string($encryptedSecret) && $encryptedSecret !== '') {
            try {
                $provider['client_secret'] = ServiceContainer::getCrypto()->decryptStandard($encryptedSecret);
            } catch (\Throwable) {
                $provider['client_secret'] = '';
            }
        }

        return $provider;
    }
}
