<?php

/**
 * User Link Service - Links IdP users to OpenEMR users
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Services;

use OpenEMR\Common\Logging\SystemLogger;

class UserLinkService
{
    private SystemLogger $logger;

    public function __construct()
    {
        $this->logger = new SystemLogger();
    }

    /**
     * Find or create link between IdP user and OpenEMR user
     *
     * @param int $providerId Database ID of the SSO provider
     * @param array $userInfo Extracted user info from IdP token
     * @param bool $autoProvision Whether to auto-create users
     * @param string|null $defaultAcl Default ACL for new users
     * @return array|null OpenEMR user data, or null if not found/created
     */
    public function findOrLinkUser(
        int $providerId,
        array $userInfo,
        bool $autoProvision = false,
        ?string $defaultAcl = null
    ): ?array {
        $providerUserId = $userInfo['sub'] ?? '';
        $email = $userInfo['email'] ?? '';

        if (empty($providerUserId)) {
            $this->logger->error('SSO: No subject identifier in user info');
            return null;
        }

        // Check if user is already linked
        $link = $this->getExistingLink($providerId, $providerUserId);
        if ($link) {
            $user = $this->getOpenEmrUser($link['user_id']);
            if ($user) {
                $this->updateLastLogin($link['id']);
                return $user;
            }
            // Link exists but user doesn't - remove stale link
            $this->removeLink($link['id']);
        }

        // Try to find OpenEMR user by email
        $user = $this->findUserByEmail($email);
        if ($user) {
            $this->createLink($providerId, $providerUserId, $user['id'], $email);
            return $user;
        }

        // Try to find OpenEMR user by username (email before @)
        $username = strstr($email, '@', true) ?: $email;
        $user = $this->findUserByUsername($username);
        if ($user) {
            $this->createLink($providerId, $providerUserId, $user['id'], $email);
            return $user;
        }

        // Auto-provision new user if enabled
        if ($autoProvision) {
            $user = $this->provisionUser($userInfo, $defaultAcl);
            if ($user) {
                $this->createLink($providerId, $providerUserId, $user['id'], $email);
                return $user;
            }
        }

        $this->logger->warning('SSO: No matching OpenEMR user found', [
            'email' => $email,
            'provider_id' => $providerId,
        ]);

        return null;
    }

    /**
     * Get existing link by provider and provider user ID
     */
    private function getExistingLink(int $providerId, string $providerUserId): ?array
    {
        $result = sqlQuery(
            "SELECT * FROM sso_user_links WHERE provider_id = ? AND provider_user_id = ?",
            [$providerId, $providerUserId]
        );
        return $result ?: null;
    }

    /**
     * Create a new user link
     */
    private function createLink(int $providerId, string $providerUserId, int $userId, string $email): void
    {
        sqlStatement(
            "INSERT INTO sso_user_links (user_id, provider_id, provider_user_id, email, linked_at, last_login)
             VALUES (?, ?, ?, ?, NOW(), NOW())",
            [$userId, $providerId, $providerUserId, $email]
        );

        $this->logger->debug('SSO: Created user link', [
            'user_id' => $userId,
            'provider_id' => $providerId,
            'email' => $email,
        ]);
    }

    /**
     * Update last login timestamp for a link
     */
    private function updateLastLogin(int $linkId): void
    {
        sqlStatement(
            "UPDATE sso_user_links SET last_login = NOW() WHERE id = ?",
            [$linkId]
        );
    }

    /**
     * Remove a stale link
     */
    private function removeLink(int $linkId): void
    {
        sqlStatement("DELETE FROM sso_user_links WHERE id = ?", [$linkId]);
    }

    /**
     * Get OpenEMR user by ID
     */
    private function getOpenEmrUser(int $userId): ?array
    {
        $result = sqlQuery(
            "SELECT id, username, authorized, see_auth, facility_id, cal_ui, active, fname, lname, email
             FROM users WHERE id = ? AND active = 1",
            [$userId]
        );
        return $result ?: null;
    }

    /**
     * Find OpenEMR user by email
     * Checks: email, google_signin_email, and email_direct fields
     */
    private function findUserByEmail(string $email): ?array
    {
        if (empty($email)) {
            return null;
        }

        $result = sqlQuery(
            "SELECT id, username, authorized, see_auth, facility_id, cal_ui, active, fname, lname, email
             FROM users
             WHERE (email = ? OR google_signin_email = ? OR email_direct = ?)
             AND active = 1",
            [$email, $email, $email]
        );
        return $result ?: null;
    }

    /**
     * Find OpenEMR user by username
     */
    private function findUserByUsername(string $username): ?array
    {
        if (empty($username)) {
            return null;
        }

        $result = sqlQuery(
            "SELECT id, username, authorized, see_auth, facility_id, cal_ui, active, fname, lname, email
             FROM users WHERE username = ? AND active = 1",
            [$username]
        );
        return $result ?: null;
    }

    /**
     * Provision a new OpenEMR user from IdP info
     */
    private function provisionUser(array $userInfo, ?string $defaultAcl): ?array
    {
        $email = $userInfo['email'] ?? '';
        $username = strstr($email, '@', true) ?: ('sso_' . substr(md5($userInfo['sub']), 0, 8));
        $fname = $userInfo['given_name'] ?? '';
        $lname = $userInfo['family_name'] ?? '';

        // Ensure username is unique
        $baseUsername = $username;
        $counter = 1;
        while ($this->findUserByUsername($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Generate a random password (user will authenticate via SSO, not this password)
        $password = bin2hex(random_bytes(32));
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // 1. Insert into groups table (required for authProvider session variable)
        // Note: groups.name is the group name shown in UI (e.g., "Default"), not the ACL value
        sqlStatement(
            "INSERT INTO `groups` (name, user) VALUES ('Default', ?)",
            [$username]
        );

        // 2. Insert new user into users table (password field is legacy, use 'NoLongerUsed')
        // Use sqlInsert() which returns the correct insert ID
        $userId = sqlInsert(
            "INSERT INTO users (username, password, fname, lname, email, authorized, active, abook_type, cal_ui)
             VALUES (?, 'NoLongerUsed', ?, ?, ?, 0, 1, '', 1)",
            [$username, $fname, $lname, $email]
        );

        if (!$userId) {
            $this->logger->error('SSO: Failed to provision user - no insert ID returned');
            return null;
        }

        $this->logger->debug('SSO: User created in users table', ['user_id' => $userId, 'username' => $username]);

        // 3. Insert into users_secure table (required for session validation)
        $this->logger->debug('SSO: Attempting users_secure insert', [
            'user_id' => $userId,
            'username' => $username,
            'password_hash_length' => strlen($passwordHash),
        ]);

        try {
            sqlStatementThrowException(
                "INSERT INTO users_secure (id, username, password, last_update_password) VALUES (?, ?, ?, NOW())",
                [$userId, $username, $passwordHash]
            );

            // Verify the insert worked
            $verifySecure = sqlQuery("SELECT id FROM users_secure WHERE id = ?", [$userId]);
            if (!$verifySecure) {
                $this->logger->error('SSO: users_secure entry not created despite no error', [
                    'user_id' => $userId,
                    'username' => $username,
                ]);
                // Try direct insert as fallback
                $GLOBALS['adodb']['db']->Execute(
                    "INSERT INTO users_secure (id, username, password, last_update_password) VALUES (?, ?, ?, NOW())",
                    [$userId, $username, $passwordHash]
                );
            } else {
                $this->logger->debug('SSO: users_secure entry verified', ['user_id' => $userId]);
            }
        } catch (\Exception $e) {
            $this->logger->error('SSO: Exception creating users_secure entry', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        // Assign to ACL group if specified
        if (!empty($defaultAcl)) {
            $this->assignUserToAcl($userId, $defaultAcl);
        }

        $this->logger->info('SSO: Provisioned new user', [
            'user_id' => $userId,
            'username' => $username,
            'email' => $email,
        ]);

        return $this->getOpenEmrUser($userId);
    }

    /**
     * Assign user to ACL group
     */
    private function assignUserToAcl(int $userId, string $aclName): void
    {
        // Get the user's info - gacl_aro.value must be USERNAME (not user id)
        // because AclMain::zhAclCheck joins gacl_aro.value to users_secure.username
        $userRecord = sqlQuery("SELECT fname, lname, username FROM users WHERE id = ?", [$userId]);
        if (!$userRecord || empty($userRecord['username'])) {
            $this->logger->error('SSO: User not found for ACL assignment', ['user_id' => $userId]);
            return;
        }

        $username = $userRecord['username'];
        $aroName = trim(($userRecord['fname'] ?? '') . ' ' . ($userRecord['lname'] ?? ''));
        if (empty($aroName)) {
            $aroName = $username;
        }

        // Find the ACL group by value
        $group = sqlQuery(
            "SELECT id FROM gacl_aro_groups WHERE value = ?",
            [$aclName]
        );

        if (!$group) {
            $this->logger->warning('SSO: ACL group not found', ['acl' => $aclName]);
            return;
        }

        $this->logger->debug('SSO: Found ACL group', ['group_id' => $group['id'], 'acl_name' => $aclName]);

        // Check if ARO already exists for this user (by username)
        $aro = sqlQuery(
            "SELECT id FROM gacl_aro WHERE section_value = 'users' AND value = ?",
            [$username]
        );

        if (!$aro) {
            // Get next ARO id (gacl_aro.id is not auto-increment)
            $maxId = sqlQuery("SELECT MAX(id) as max_id FROM gacl_aro");
            $nextAroId = ($maxId['max_id'] ?? 0) + 1;

            // Create ARO for user with explicit id - value is USERNAME
            sqlStatement(
                "INSERT INTO gacl_aro (id, section_value, value, name) VALUES (?, 'users', ?, ?)",
                [$nextAroId, $username, $aroName]
            );

            // Fetch the newly created ARO
            $aro = sqlQuery(
                "SELECT id FROM gacl_aro WHERE section_value = 'users' AND value = ?",
                [$username]
            );

            if (!$aro) {
                $this->logger->error('SSO: Failed to create ARO for user', ['user_id' => $userId]);
                return;
            }

            $this->logger->debug('SSO: Created ARO for user', ['aro_id' => $aro['id'], 'user_id' => $userId]);
        }

        // Check if mapping already exists
        $existingMap = sqlQuery(
            "SELECT * FROM gacl_groups_aro_map WHERE group_id = ? AND aro_id = ?",
            [$group['id'], $aro['id']]
        );

        if (!$existingMap) {
            // Assign ARO to group
            sqlStatement(
                "INSERT INTO gacl_groups_aro_map (group_id, aro_id) VALUES (?, ?)",
                [$group['id'], $aro['id']]
            );

            $this->logger->info('SSO: Assigned user to ACL group', [
                'user_id' => $userId,
                'aro_id' => $aro['id'],
                'group_id' => $group['id'],
                'acl_name' => $aclName,
            ]);
        } else {
            $this->logger->debug('SSO: User already in ACL group', ['user_id' => $userId, 'acl_name' => $aclName]);
        }
    }

    /**
     * Get all links for a user
     */
    public function getUserLinks(int $userId): array
    {
        $links = [];
        $results = sqlStatement(
            "SELECT l.*, p.name as provider_name, p.provider_type
             FROM sso_user_links l
             JOIN sso_providers p ON l.provider_id = p.id
             WHERE l.user_id = ?",
            [$userId]
        );

        while ($row = sqlFetchArray($results)) {
            $links[] = $row;
        }

        return $links;
    }

    /**
     * Unlink a user from a provider
     */
    public function unlinkUser(int $userId, int $providerId): bool
    {
        sqlStatement(
            "DELETE FROM sso_user_links WHERE user_id = ? AND provider_id = ?",
            [$userId, $providerId]
        );
        return true;
    }
}
