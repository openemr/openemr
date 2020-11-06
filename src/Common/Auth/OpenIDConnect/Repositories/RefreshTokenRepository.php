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

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\RefreshTokenEntity;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        // Some logic to persist the refresh token in a database
    }

    public function revokeRefreshToken($tokenId)
    {
        // Some logic to revoke the refresh token in a database
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        return false; // The refresh token has not been revoked
    }

    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity();
    }
}
