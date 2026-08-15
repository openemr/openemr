<?php

/**
 * Persists external OIDC subject to local user bindings.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ExternalIdp\Repository;

final class IdentityRepository
{
    public function findUserId(int $providerId, string $subject): ?int
    {
        $row = sqlQuery(
            'SELECT i.`user_id`
             FROM `module_external_idp_identity` AS i
             INNER JOIN `users` AS u ON u.`id` = i.`user_id`
             WHERE i.`provider_id` = ? AND i.`subject` = ? AND u.`active` = 1',
            [$providerId, $subject]
        );

        return !empty($row['user_id']) ? (int) $row['user_id'] : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForSite(string $siteId): array
    {
        $rows = sqlStatement(
            'SELECT i.`id`, i.`provider_id`, i.`subject`, i.`user_id`, i.`created_at`, i.`updated_at`,
                    p.`display_name`, p.`issuer_url`,
                    u.`username`, u.`fname`, u.`lname`, u.`active`
             FROM `module_external_idp_identity` AS i
             INNER JOIN `module_external_idp_provider` AS p ON p.`id` = i.`provider_id`
             INNER JOIN `users` AS u ON u.`id` = i.`user_id`
             WHERE p.`site_id` = ?
             ORDER BY p.`display_name`, u.`lname`, u.`fname`, i.`subject`',
            [$siteId]
        );

        $bindings = [];
        while ($row = sqlFetchArray($rows)) {
            $bindings[] = $row;
        }
        return $bindings;
    }

    public function saveBinding(int $providerId, string $subject, int $userId): void
    {
        if ($providerId < 1 || $userId < 1 || trim($subject) === '') {
            throw new \InvalidArgumentException('Provider ID, subject, and user ID are required.');
        }

        $user = sqlQuery('SELECT `id`, `active` FROM `users` WHERE `id` = ?', [$userId]);
        if (empty($user) || (int) ($user['active'] ?? 0) !== 1) {
            throw new \RuntimeException('Select an active OpenEMR user before creating the binding.');
        }

        sqlStatement(
            'INSERT INTO `module_external_idp_identity`
                (`provider_id`, `subject`, `user_id`)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
                `user_id` = VALUES(`user_id`),
                `updated_at` = CURRENT_TIMESTAMP',
            [$providerId, $subject, $userId]
        );
    }

    public function deleteBinding(int $bindingId): void
    {
        if ($bindingId < 1) {
            throw new \InvalidArgumentException('Binding ID is required.');
        }

        sqlStatement('DELETE FROM `module_external_idp_identity` WHERE `id` = ?', [$bindingId]);
    }
}
