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
}
