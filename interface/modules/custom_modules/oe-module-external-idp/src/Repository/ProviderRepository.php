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
    /** @return array<string, mixed>|false */
    public function getForSite(string $siteId): array|false
    {
        return sqlQuery('SELECT * FROM `module_external_idp_provider` WHERE `site_id` = ?', [$siteId]);
    }

    /** @return array<string, mixed>|false */
    public function getEnabledForSite(string $siteId): array|false
    {
        return sqlQuery('SELECT * FROM `module_external_idp_provider` WHERE `site_id` = ? AND `enabled` = 1', [$siteId]);
    }

    /** @return array<string, mixed>|false */
    public function getById(int $id): array|false
    {
        return sqlQuery('SELECT * FROM `module_external_idp_provider` WHERE `id` = ?', [$id]);
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
    public function save(string $siteId, string $displayName, string $issuerUrl, string $clientId, string $clientSecret, string $scopes, bool $enabled, array $metadata): void
    {
        $encryptedSecret = $clientSecret === '' ? null : ServiceContainer::getCrypto()->encryptForDatabase($clientSecret);
        $metadataJson = json_encode($metadata, JSON_THROW_ON_ERROR);
        sqlStatement(
            'INSERT INTO `module_external_idp_provider`
                (`site_id`, `display_name`, `issuer_url`, `client_id`, `client_secret`, `scopes`, `discovery_document`, `discovery_fetched_at`, `enabled`)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)
             ON DUPLICATE KEY UPDATE
                `display_name` = VALUES(`display_name`),
                `issuer_url` = VALUES(`issuer_url`),
                `client_id` = VALUES(`client_id`),
                `client_secret` = COALESCE(VALUES(`client_secret`), `client_secret`),
                `scopes` = VALUES(`scopes`),
                `discovery_document` = VALUES(`discovery_document`),
                `discovery_fetched_at` = NOW(),
                `enabled` = VALUES(`enabled`)',
            [$siteId, $displayName, $issuerUrl, $clientId, $encryptedSecret, $scopes, $metadataJson, $enabled ? 1 : 0]
        );
    }
}
