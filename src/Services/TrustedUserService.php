<?php

/**
 * TrustedUserService handles CRUD operations for OAUTH2 Trusted Users.  A Trusted User represents an authorized
 * oauth2 connection that we use to validate against inside of OpenEMR.  Trusted User's can be revoked / removed which
 * prevents the associated client / user app from using their access tokens.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;

class TrustedUserService
{
    public function isTrustedUser($clientId, $userId)
    {
            $trusted = $this->getTrustedUser($clientId, $userId);
            $isTrusted = !empty($trusted['session_cache']);
            return $isTrusted;
    }

    public function getTrustedUsersForClient($clientId)
    {
        $records = QueryUtils::fetchRecords("SELECT * FROM `oauth_trusted_user` WHERE `client_id`= ?", array($clientId));
        return $records;
    }

    public function getTrustedUser($clientId, $userId)
    {
        $trusted = sqlQueryNoLog("SELECT * FROM `oauth_trusted_user` WHERE `client_id`= ? AND `user_id`= ?", array($clientId, $userId));
        return $trusted;
    }

    public function getTrustedUserByCode($code)
    {
        return sqlQueryNoLog("SELECT * FROM `oauth_trusted_user` WHERE `code`= ?", array($code));
    }

    public function saveTrustedUser($clientId, $userId, $scope, $persist, $code = '', $session = '', $grant = 'authorization_code')
    {
        if (\is_array($scope)) {
            $scope = implode(" ", $scope);
        }
        if (empty($userId)) {
            throw new \InvalidArgumentException("userId cannot be null unless this is a client_credentials grant");
        }
        $id = $this->getTrustedUser($clientId, $userId)['id'] ?? '';
        $sql = "REPLACE INTO `oauth_trusted_user` (`id`, `user_id`, `client_id`, `scope`, `persist_login`, `time`, `code`, session_cache, `grant_type`) VALUES (?, ?, ?, ?, ?, Now(), ?, ?, ?)";

        return sqlQueryNoLog($sql, array($id, $userId, $clientId, $scope, $persist, $code, $session, $grant));
    }

    public function deleteTrustedUserById($id)
    {
        return sqlQueryNoLog("DELETE FROM `oauth_trusted_user` WHERE `oauth_trusted_user`.`id` = ?", array($id));
    }
}
