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
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\AccessTokenEntity;
use OpenEMR\Common\Auth\OpenIDConnect\SMARTSessionTokenContextBuilder;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * @var SMARTSessionTokenContextBuilder
     */
    private $builder;

    /**
     * The context values to use for issuing new tokens.  This is populated when a refresh grant is generating a new
     * access token.  We have to use our existing values.
     * @var array
     */
    private $contextForNewTokens;

    public function __construct()
    {
        $this->builder = new SMARTSessionTokenContextBuilder($_SESSION ?? []);
        $this->contextForNewTokens = null;
    }

    /**
     * Returns the token expiration date for the given token id.
     * @param $tokenId string The access token id
     * @param $clientId string The client id
     * @param number $userId  The id of the openemr user the token corresponds to
     * @return string|null The expiration date or null if there was no token found
     */
    public function getTokenExpiration($tokenId, $clientId, $userId = null)
    {
        $result = sqlQueryNoLog("SELECT `expiry` FROM `api_token` WHERE `token` = ? AND `client_id` = ? AND `user_id` = ?", [$tokenId, $clientId, $userId]);
        $authTokenExpiration = $result['expiry'] ?? null;
        return $authTokenExpiration;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $token = $this->getTokenByToken($accessTokenEntity->getIdentifier());
        if (!empty($token)) {
            throw UniqueTokenIdentifierConstraintViolationException::create("Duplicate id was generated");
        }

        $access_token = (string) $accessTokenEntity;
        $exp_date = $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s');
        $user_id = $accessTokenEntity->getUserIdentifier();
        $unique_id = $accessTokenEntity->getIdentifier();
        $client_id = $accessTokenEntity->getClient()->getIdentifier();
        $scopes = $accessTokenEntity->getScopes();
        $context = $this->getContextForNewAccessTokens($scopes);
        $scope = \json_encode($scopes);
        $encodedContext = \json_encode($context);

        $sql = " INSERT INTO api_token SET";
        $sql .= " `user_id` = ?,";
        $sql .= " `token` = ?,";
        $sql .= " `expiry` = ?, `client_id` = ?, `scope` = ?, `context` = ? ";
        sqlStatementNoLog($sql, [$user_id, $unique_id, $exp_date, $client_id, $scope, $encodedContext]);
    }

    public function revokeAccessToken($tokenId)
    {
        (new SystemLogger())->debug(self::class . "->revokeAccessToken() attempting to revoke access token ", ['tokenId' => $tokenId]);
        // Some logic to revoke the refresh token in a database
        $sql = "UPDATE api_token SET revoked = 1 WHERE token = ?";
        QueryUtils::sqlStatementThrowException($sql, [$tokenId], true);
    }

    /**
     * Because of the way our access tokens contain the multi-tenant site-id in them we have to return false on this statement
     * as we currently don't have any database functionality loaded and can't check the database to see if the token is revoked
     * Since this logic is embedded inside the League OAUTH server we have it return false and check the token revokation
     * later on in our api dispatch logic.
     * @param string $tokenId
     * @return bool
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return false;
    }

    public function isAccessTokenRevokedInDatabase($tokenId)
    {
        $sql = " SELECT * FROM api_token WHERE token = ? AND revoked = 1 ";
        $resource = QueryUtils::sqlStatementThrowException($sql, [$tokenId], true);
        $result = QueryUtils::fetchArrayFromResultSet($resource);
        return !empty($result); // if the result set is not empty then its been revoked, otherwise its a good token
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

    public function getActiveTokensForUser($clientId, $userUuid)
    {
        // note user_id is the STRING representation of the uuid, not the binary representation
        $sql = "SELECT * FROM api_token WHERE user_id = ? AND client_id = ? AND expiry > NOW() AND revoked = 0 ";
        return QueryUtils::fetchRecords($sql, [$userUuid, $clientId]);
    }

    /**
     * Retrieves a token record for given database token id.
     * @param $id The database identifier for the token (see table api_token.id
     * @return array|null
     */
    public function getTokenById($id)
    {
        $sql = "SELECT * FROM api_token WHERE id = ? ";
        $records = QueryUtils::fetchRecords($sql, [$id]);
        return $records[0] ?? null;
    }

    public function getTokenByToken($token)
    {
        $sql = "SELECT * FROM api_token WHERE token = ? ";
        $records = QueryUtils::fetchRecords($sql, [$token], true);
        return $records[0] ?? null;
    }

    /**
     * Sets the context array that will be saved to the database for new acess tokens.
     * @param $context The array of context variables.  If this is not an array the context is set to null;
     */
    public function setContextForNewTokens($context)
    {
        $this->contextForNewTokens = is_array($context) && !empty($context) ? $context : null;
    }

    /**
     * Retrieves the context to use for new access tokens based upon the passed in scopes.  It will use the existing
     * context saved in the repositoryor will build a new context from the passed in scopes.
     * @param $scopes The scopes in the access token that determines what context variables to use in the access token
     * @return array The built context session.
     */
    private function getContextForNewAccessTokens($scopes)
    {
        if (!empty($this->contextForNewTokens)) {
            $context = $this->builder->getContextForScopesWithExistingContext($this->contextForNewTokens, $scopes) ?? [];
        } else {
            $context = $this->builder->getContextForScopes($scopes) ?? [];
        }
        return $context;
    }
}
