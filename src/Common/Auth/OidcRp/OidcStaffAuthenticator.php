<?php

/**
 * Completes staff login after a verified ID token.
 *
 * Existing OpenEMR users keep their groups and ACL. Optional user creation
 * inserts a local user into the configured OpenEMR group and ACL so an
 * administrator can later assign permissions the same way as for any other user.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman
 * @copyright Copyright (c) 2026 Tamir Suliman
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\UserService;

final class OidcStaffAuthenticator
{
    public function __construct(
        private readonly OidcRpSettings $settings,
        private readonly OidcIdentityRepository $identities,
    ) {
    }

    public function login(OidcIdTokenClaims $claims): void
    {
        $username = $claims->username($this->settings->usernameClaim);
        $this->assertUsableUsername($username);

        $user = $this->resolveUser($claims, $username);
        $userService = new UserService();
        $authGroup = $userService->getAuthGroupForUser($user['username']);
        if ($authGroup === false || $authGroup === '') {
            throw new OidcRpException('OpenEMR user does not belong to a group');
        }
        $aclGroups = AclExtended::aclGetGroupTitles($user['username']);
        if ($aclGroups === 0 || $aclGroups === null || $aclGroups === []) {
            throw new OidcRpException('OpenEMR user is not in an access-control group');
        }

        $secure = QueryUtils::querySingleRow(
            'SELECT `password` FROM `users_secure` WHERE BINARY `username` = ?',
            [$user['username']],
        );
        $hash = is_array($secure) ? ($secure['password'] ?? null) : null;
        if (!is_string($hash) || $hash === '') {
            throw new OidcRpException('OpenEMR user is not configured for login');
        }

        $this->identities->bind((int) $user['id'], $claims);

        $ip = collectIpAddresses();
        EventAuditLogger::getInstance()->newEvent(
            'login',
            $user['username'],
            $authGroup,
            1,
            "Auth success via OIDC SSO : " . $ip['ip_string'],
        );
        AuthUtils::setUserSessionVariables($user['username'], $hash, $user, $authGroup);
    }

    /**
     * @return array{id: int|string, username: string, authorized: mixed, see_auth: mixed, active: mixed}
     */
    private function resolveUser(OidcIdTokenClaims $claims, string $username): array
    {
        $boundId = $this->identities->findUserId($claims->issuer, $claims->subject);
        if ($boundId !== null) {
            return $this->requireActiveLoginUser($this->findUserById($boundId));
        }

        $byUsername = $this->findUserByUsername($username);
        if ($byUsername !== null) {
            return $this->requireActiveLoginUser($byUsername);
        }

        if ($this->settings->matchEmail && $claims->email !== '') {
            $byEmail = $this->findUserByEmail($claims->email);
            if ($byEmail !== null) {
                return $this->requireActiveLoginUser($byEmail);
            }
        }

        if ($this->settings->createUsers) {
            return $this->createUser($claims, $username);
        }

        throw new OidcRpException('No OpenEMR user matches this identity provider account');
    }

    /**
     * @param array<mixed>|null $user
     * @return array{id: int|string, username: string, authorized: mixed, see_auth: mixed, active: mixed}
     */
    private function requireActiveLoginUser(?array $user): array
    {
        if ($user === null) {
            throw new OidcRpException('No OpenEMR user matches this identity provider account');
        }
        $username = $user['username'] ?? null;
        $id = $user['id'] ?? null;
        if (!is_string($username) || $username === '') {
            throw new OidcRpException('No OpenEMR user matches this identity provider account');
        }
        if (is_int($id)) {
            $userId = $id;
        } elseif (is_string($id) && ctype_digit($id)) {
            $userId = $id;
        } else {
            throw new OidcRpException('No OpenEMR user matches this identity provider account');
        }
        $active = $user['active'] ?? 0;
        if (!is_numeric($active) || (int) $active !== 1) {
            throw new OidcRpException('OpenEMR user is not active');
        }

        return [
            'id' => $userId,
            'username' => $username,
            'authorized' => $user['authorized'] ?? 0,
            'see_auth' => $user['see_auth'] ?? 1,
            'active' => $user['active'] ?? 1,
        ];
    }

    /**
     * @return array<mixed>|null
     */
    private function findUserById(int $userId): ?array
    {
        $row = QueryUtils::querySingleRow(
            'SELECT `id`, `username`, `authorized`, `see_auth`, `active` FROM `users` WHERE `id` = ?',
            [$userId],
        );
        return $row === false ? null : $row;
    }

    /**
     * @return array<mixed>|null
     */
    private function findUserByUsername(string $username): ?array
    {
        $row = QueryUtils::querySingleRow(
            'SELECT `id`, `username`, `authorized`, `see_auth`, `active` FROM `users` WHERE BINARY `username` = ?',
            [$username],
        );
        return $row === false ? null : $row;
    }

    /**
     * @return array<mixed>|null
     */
    private function findUserByEmail(string $email): ?array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `id`, `username`, `authorized`, `see_auth`, `active` FROM `users` WHERE `email` = ? AND `username` IS NOT NULL AND `username` != ?',
            [$email, ''],
        );
        if (count($rows) !== 1) {
            return null;
        }
        return $rows[0];
    }

    /**
     * @return array{id: int|string, username: string, authorized: mixed, see_auth: mixed, active: mixed}
     */
    private function createUser(OidcIdTokenClaims $claims, string $username): array
    {
        $userData = [
            'username' => $username,
            'password' => 'NoLongerUsed',
            'fname' => $claims->firstName(),
            'lname' => $claims->lastName(),
            'email' => $claims->email !== '' ? $claims->email : null,
            'active' => 1,
            'authorized' => 0,
            'see_auth' => 1,
            'calendar' => 0,
        ];
        $columns = array_map(static fn(string $col): string => '`' . $col . '`', array_keys($userData));
        $placeholders = array_fill(0, count($userData), '?');
        $userId = QueryUtils::sqlInsert(
            'INSERT INTO `users` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')',
            array_values($userData),
        );

        $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();
        QueryUtils::sqlStatementThrowException('UPDATE `users` SET `uuid` = ? WHERE `id` = ?', [$uuid, $userId]);

        $randomPassword = bin2hex(random_bytes(32));
        $authHash = new AuthHash();
        $hash = $authHash->passwordHash($randomPassword);
        if (!is_string($hash) || $hash === '') {
            throw new OidcRpException('Unable to create a local credential for the new user');
        }
        QueryUtils::sqlInsert(
            'INSERT INTO `users_secure` (`id`, `username`, `password`, `last_update_password`) VALUES (?, ?, ?, NOW())',
            [$userId, $username, $hash],
        );
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `groups` SET `name` = ?, `user` = ?',
            [$this->settings->newUserGroup, $username],
        );
        AclExtended::setUserAro(
            [$this->settings->newUserAcl],
            $username,
            $claims->firstName(),
            '',
            $claims->lastName(),
        );

        $created = $this->findUserById($userId);
        return $this->requireActiveLoginUser($created);
    }

    private function assertUsableUsername(string $username): void
    {
        if ($username === '' || str_contains($username, ' ') || strlen($username) > 255) {
            throw new OidcRpException('Identity provider username is not usable as an OpenEMR login');
        }
    }
}
