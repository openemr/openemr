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

use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\UserEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\UserRepository;

/**
 * @deprecated Use UserRepository instead.
 */
class IdentityRepository implements IdentityProviderInterface
{
    /**
     * @param $identifier
     * @deprecated Use UserRepository::getUserEntityByIdentifier() instead.
     * @return UserEntity
     */
    public function getUserEntityByIdentifier($identifier)
    {
        $userId = new UserEntity();
        $userId->identifier = $identifier;
        return $userId;
    }
}
