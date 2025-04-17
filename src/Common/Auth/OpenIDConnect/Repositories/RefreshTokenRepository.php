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
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\RefreshTokenEntity;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var boolean
     */
    private $issueNewRefreshToken;

    /**
     * RefreshTokenRepository constructor
     * @param bool $issueNewRefreshToken Whether a new refresh token should be issued when called.
     */
    public function __construct($issueNewRefreshToken = true)
    {
        $this->issueNewRefreshToken = $issueNewRefreshToken === true;
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $token = $this->getTokenByToken($refreshTokenEntity->getIdentifier());
        if (!empty($token)) {
            throw UniqueTokenIdentifierConstraintViolationException::create("Duplicate id was generated");
        }

        $exp_date = $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s');
        $user_id = $refreshTokenEntity->getAccessToken()->getUserIdentifier();
        $unique_id = $refreshTokenEntity->getIdentifier();
        $client_id = $refreshTokenEntity->getAccessToken()->getClient()->getIdentifier();

        // TODO: Do we need to throw the UniqueTokenIdentifierConstraintViolationException?? if our token is already used?
        $sql = " INSERT INTO api_refresh_token SET";
        $sql .= " `user_id` = ?,";
        $sql .= " `token` = ?,";
        $sql .= " `expiry` = ?, `client_id` = ? ";
        sqlStatementNoLog($sql, [$user_id, $unique_id, $exp_date, $client_id]);
    }

    public function revokeRefreshToken($tokenId)
    {
        // Some logic to revoke the refresh token in a database
        (new SystemLogger())->debug(self::class . "->revokeRefreshToken() attempting to revoke refresh token ", ['tokenId' => $tokenId]);
        $sql = "UPDATE api_refresh_token SET revoked = 1 WHERE token = ?";
        QueryUtils::sqlStatementThrowException($sql, [$tokenId], true);
    }

    public function isRefreshTokenRevoked($tokenId)
    {
        $sql = " SELECT * FROM api_refresh_token WHERE token = ? AND revoked = 1 ";
        $resource = QueryUtils::sqlStatementThrowException($sql, [$tokenId], true);
        $result = QueryUtils::fetchArrayFromResultSet($resource);
        return !empty($result); // if the result set is not empty then its been revoked, otherwise its a good token
    }

    /**
     * Returns a RefreshToken if the repository's issueNewRefreshToken property is set to true.  Certain scopes like
     * offline_access determines whether a refresh token is issued to the requesting client.  If the scope is not
     * authorized we do not issue a refresh token for the app to have offline access.
     * @return RefreshTokenEntityInterface|RefreshTokenEntity|null
     */
    public function getNewRefreshToken()
    {
        if ($this->issueNewRefreshToken) {
            return new RefreshTokenEntity();
        } else {
            return null;
        }
    }

    public function getActiveTokensForUser($clientId, $userUuid)
    {
        // note user_id is the STRING representation of the uuid, not the binary representation
        $sql = "SELECT * FROM api_refresh_token WHERE user_id = ? AND client_id = ? AND expiry > NOW() AND revoked = 0 ";
        return QueryUtils::fetchRecords($sql, [$userUuid, $clientId]);
    }

    public function getTokenById($id)
    {
        $sql = " SELECT * FROM api_refresh_token WHERE id = ?";
        $resource = QueryUtils::sqlStatementThrowException($sql, [$id], true);
        $result = QueryUtils::fetchArrayFromResultSet($resource);
        return $result ?? null; // if the result set is not empty then its been revoked, otherwise its a good token
    }

    public function getTokenByToken($token)
    {
        $sql = " SELECT * FROM api_refresh_token WHERE token = ?";
        $resource = QueryUtils::sqlStatementThrowException($sql, [$token], true);
        $result = QueryUtils::fetchArrayFromResultSet($resource);
        return $result ?? null; // if the result set is not empty then its been revoked, otherwise its a good token
    }
}
