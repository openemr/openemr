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

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AccessTokenEntity;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{

    /**
     * Returns the token expiration date for the given token id.  If $userId is null it will return the token for a
     * confidential_client access token
     * @param $tokenId string The access token id
     * @param $clientId string The client id
     * @param number|null $userId  The id of the openemr user the token corresponds to
     * @return string|null The expiration date or null if there was no token found
     */
    public function getTokenExpiration($tokenId, $clientId, $userId = null)
    {
        if (empty($userId)) {
            $result = sqlQueryNoLog("SELECT `expiry` FROM `api_token` WHERE `token` = ? AND `client_id` = ? AND `user_id` IS NULL", [$tokenId, $clientId]);
        } else {
            $result = sqlQueryNoLog("SELECT `expiry` FROM `api_token` WHERE `token` = ? AND `client_id` = ? AND `user_id` = ?", [$tokenId, $clientId, $userId]);
        }
        $authTokenExpiration = $result['expiry'] ?? null;
        return $authTokenExpiration;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $access_token = (string) $accessTokenEntity;
        $exp_date = $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s');
        $user_id = $accessTokenEntity->getUserIdentifier();
        $unique_id = $accessTokenEntity->getIdentifier();
        $client_id = $accessTokenEntity->getClient()->getIdentifier();
        $scope = \json_encode($accessTokenEntity->getScopes());

        $sql = " INSERT INTO api_token SET";
        $sql .= " `user_id` = ?,";
        $sql .= " `token` = ?,";
        $sql .= " `expiry` = ?, `client_id` = ?, `scope` = ?";
        sqlStatementNoLog($sql, [$user_id, $unique_id, $exp_date, $client_id, $scope]);
    }

    public function revokeAccessToken($tokenId)
    {
    }

    public function isAccessTokenRevoked($tokenId)
    {
        return false; // Access token hasn't been revoked
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);

        return $accessToken;
    }
}
