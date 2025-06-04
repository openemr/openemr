<?php

/**
 * JWTRepository.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use OpenEMR\Common\Database\QueryUtils;

class JWTRepository
{
    /**
     * Retrieves the jwt grant history for a given jti and expiration.  If the expiration is null it returns all records
     * for that jti.  If an expiration is provided it will return only jti records that are have not expired within the given time frame.
     * @param $jti string
     * @param int|null $expiration timestamp in milliseconds of when the jti should expire
     * @return array
     */
    public function getJwtGrantHistoryForJTI($jti, $expiration = null)
    {
        $sql = "select * FROM jwt_grant_history WHERE jti = ?";
        $params = [$jti];
        if (!empty($expiration)) {
            $sql .= " AND jti_exp > ?";
            $params[] = $expiration;
        }
        $records = QueryUtils::fetchRecords($sql, $params, true);
        return $records;
    }

    /**
     * Saves off a historical record of the JWT unique id that was used for requesting a grant access token.  By saving
     * off the client's requesting JTI and the date the JWT is valid for we can avoid replay attacks.
     * @param $jti string The unique JWT token id that was used for requesting a grant access token
     * @param $client_id string the client id that the jwt was requested from
     * @param $expiration int|null timestamp in milliseconds of when the jti should expire
     */
    public function saveJwtHistory($jti, $client_id, $expiration)
    {
        $sql = "INSERT INTO jwt_grant_history (jti, client_id, `jti_exp`, `creation_date`) VALUES(?, ?, FROM_UNIXTIME(?), NOW())";
        QueryUtils::sqlStatementThrowException($sql, [$jti, $client_id, $expiration], true);
    }
}
