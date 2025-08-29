<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\UserEntity;

class UserRepository extends UserEntity implements UserRepositoryInterface
{
    public function getCustomUserEntityByUserCredentials(
        $userrole,
        $username,
        $password,
        $email,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $user = new UserEntity();
        if (!empty($userrole) && !empty($username) && !empty($password)) {
            if (!$user->getAccountByPassword($userrole, $username, $password, $email)) {
                return false;
            }

            return $user;
        }
        return false;
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        return false;
    }

    public function getUserEntityByEmail(string $email): ?UserEntity
    {
        $userRecord = sqlQueryNoLog("SELECT * FROM `users` WHERE `email` = ?", [$email]);
        if (empty($userRecord)) {
            return null;
        }

        $user = new UserEntity();
        $user->setIdentifier($userRecord['uuid']);
        return $user;
    }

    public function createUser(string $email, string $firstName, string $lastName): string
    {
        // In a real scenario, you would want to set a secure password, handle roles, etc.
        // For this example, we'll create a basic user.
        $sql = "INSERT INTO `users` (`username`, `email`, `fname`, `lname`, `active`, `authorized`) VALUES (?, ?, ?, ?, 1, 1)";
        sqlQueryNoLog($sql, [$email, $email, $firstName, $lastName]);
        $newUserId = sqlInsertId();

        // Ensure UUID is generated for the new user
        \OpenEMR\Common\Uuid\UuidRegistry::createMissingUuidsForTables(['users']);

        $userRecord = sqlQueryNoLog("SELECT `uuid` FROM `users` WHERE `id` = ?", [$newUserId]);
        return $userRecord['uuid'];
    }
}

