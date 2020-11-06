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
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\UserEntity;
use OpenEMR\Common\Uuid\UuidRegistry;

class UserRepository extends UserEntity implements UserRepositoryInterface
{
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $user = new UserEntity();
        if ($username && $password) {
            $user->getAccountByPassword($username, $password);
        }
        return $user;
    }
}
