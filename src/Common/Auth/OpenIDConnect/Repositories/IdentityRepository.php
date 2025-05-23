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

class IdentityRepository implements IdentityProviderInterface
{
    public function getUserEntityByIdentifier($identifier)
    {
        $userId = new UserEntity();
        $userId->identifier = $identifier;
        return $userId;
    }
}
