<?php

/**
 * TrustedUserService handles CRUD operations for OAUTH2 Trusted Users.  A Trusted User represents an authorized
 * oauth2 connection that we use to validate against inside of OpenEMR.  Trusted User's can be revoked / removed which
 * prevents the associated client / user app from using their access tokens.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;

class TrustedUserService
{
    public function isTrustedUser($clientId, $userId)
    {
            $trusted = $this->getTrustedUser($clientId, $userId);
            $isTrusted = !empty($trusted['session_cache']);
            return $isTrusted;
    }

    public function getTrustedUsersForClient($clientId): array
    {
        $records = QueryUtils::fetchRecords("SELECT * FROM `oauth_trusted_user` WHERE `client_id`= ?", [$clientId]);
        return $records;
    }

    public function getTrustedUser($clientId, $userId): array|false
    {
        $trusted = QueryUtils::querySingleRow("SELECT * FROM `oauth_trusted_user` WHERE `client_id`= ? AND `user_id`= ?", [$clientId, $userId], log: false);
        return $trusted;
    }

    public function getTrustedUserByCode($code): array|false
    {
        return QueryUtils::querySingleRow("SELECT * FROM `oauth_trusted_user` WHERE `code`= ?", [$code], log: false);
    }

    /**
     * @param $clientId
     * @param $userId
     * @param $scope
     * @param $persist
     * @param $code
     * @param $session
     * @param $grant
     * @throws SqlQueryException If the query fails
     * @return int The ID of the trusted user record
     */
    public function saveTrustedUser($clientId, $userId, $scope, $persist, $code = '', $session = '', $grant = 'authorization_code'): int
    {
        if (\is_array($scope)) {
            $scope = implode(" ", $scope);
        }
        if (empty($userId)) {
            throw new \InvalidArgumentException("userId cannot be null unless this is a client_credentials grant");
        }
        $id = $this->getTrustedUser($clientId, $userId)['id'] ?? '';
        $bind = [$userId, $clientId, $scope, $persist, $code, $session, $grant];
        if (empty($id)) {
            $sql = "INSERT INTO `oauth_trusted_user` (`user_id`, `client_id`, `scope`, `persist_login`, `time`, `code`, session_cache, `grant_type`) VALUES (?, ?, ?, ?, Now(), ?, ?, ?)";
            $id = QueryUtils::sqlInsert($sql, $bind);
        } else {
            $sql = "UPDATE `oauth_trusted_user` SET `user_id` = ?, `client_id` = ?, `scope` = ?, `persist_login` = ?, `time` = Now(), `code` = ?, session_cache = ?, `grant_type` = ? WHERE `oauth_trusted_user`.`id` = ?";
            $bind[] = $id;
            QueryUtils::sqlStatementThrowException($sql, $bind);
        }
        return intval($id);
    }

    public function deleteTrustedUserById($id)
    {
        // The admin "Revoke User" action offboards a specific OAuth app's
        // hold on a user; it should close the API side of that one
        // relationship. Deleting only the trusted-user row leaves the
        // user's api_refresh_token / api_token rows for that client in
        // place, which become usable again as soon as the user
        // re-authorizes the same app (a fresh oauth_trusted_user row
        // re-admits the old refresh token). Revoke the removed client's
        // tokens alongside the delete so revoke means revoke.
        //
        // Two ordering invariants:
        //   1. Scope to (user, client), not user-wide. Removing one app's
        //      record must not revoke the user's tokens for every other
        //      app they authorized. The account-lifecycle paths (admin
        //      deactivation, password change) still revoke user-wide
        //      because those events invalidate the identity itself.
        //   2. Revoke before DELETE. If the revoke throws, the
        //      trusted-user row stays in place and a retry runs the
        //      whole operation again; the alternative (delete-first)
        //      leaves orphaned active tokens on any revoke failure,
        //      with no retry path.
        $row = QueryUtils::querySingleRow(
            "SELECT `user_id`, `client_id` FROM `oauth_trusted_user` WHERE `id` = ?",
            [$id],
            log: false
        );
        if (
            is_array($row)
            && isset($row['user_id'], $row['client_id'])
            && is_string($row['user_id'])
            && is_string($row['client_id'])
        ) {
            AuthUtils::revokeOAuthTokensForUserUuidAndClient($row['user_id'], $row['client_id']);
        }
        QueryUtils::sqlStatementThrowException("DELETE FROM `oauth_trusted_user` WHERE `oauth_trusted_user`.`id` = ?", [$id], noLog: true);
    }
}
