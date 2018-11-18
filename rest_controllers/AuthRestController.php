<?php
/**
 * AuthRestController
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\RestControllers;

require_once("{$GLOBALS['srcdir']}/authentication/common_operations.php");

/*
TODO: Add to migration scripts
CREATE TABLE `api_token` (
    `id`           bigint(20) NOT NULL AUTO_INCREMENT,
    `user_id`      bigint(20) NOT NULL,
    `token`        varchar(256) DEFAULT NULL,
    `expiry`       datetime NULL,
     PRIMARY KEY (`id`)
    ) ENGINE = InnoDB;
*/

class AuthRestController
{
    public function __construct()
    {
    }

    public function authenticate($authPayload)
    {
        $is_valid = confirm_user_password($authPayload["username"], $authPayload["password"]);

        if (!$is_valid) {
            http_response_code(401);
            return;
        }

        $user = sqlQuery("SELECT id FROM users_secure WHERE username = ?", array($authPayload['username']));

        $sql = " INSERT INTO api_token SET";
        $sql .= "     user_id='" . add_escape_custom($user["id"]) . "',";
        $sql .= "     token=(SELECT LEFT(SHA2(CONCAT(NOW(), RAND(), UUID()), 512), 32)),";
        $sql .= "     expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR)";

        sqlInsert($sql);

        $token = sqlQuery("SELECT token FROM api_token WHERE user_id = ? ORDER BY id DESC", array($user["id"]));
        $encoded_site = bin2hex($_SESSION['site_id']);
        http_response_code(200);
        return $token["token"] . $encoded_site;
    }

    public function isValidToken($token)
    {
        $tokenResult = sqlQuery("SELECT user_id, token, expiry FROM api_token WHERE token=?", array($token));

        if (!$tokenResult) {
            return false;
        }

        $currentDateTime = date("Y-m-d H:i:s");
        $expiryDateTime = date("Y-m-d H:i:s", strtotime($tokenResult['expiry']));

        if ($expiryDateTime <= $currentDateTime) {
            return false;
        }

        return true;
    }

    public function getUserFromToken($token)
    {
        $sql = " SELECT";
        $sql .= " u.username";
        $sql .= " FROM api_token a";
        $sql .= " JOIN users_secure u ON u.id = a.user_id";
        $sql .= " WHERE a.token = ?";

        $userResult = sqlQuery($sql, array($token));
        return $userResult["username"];
    }

    public function aclCheck($token, $section, $value)
    {
        $username = $this->getUserFromToken($token);
        return acl_check($section, $value, $username);
    }

    public function optionallyAddMoreTokenTime($token)
    {
        $tokenResult = sqlQuery("SELECT user_id, token, expiry FROM api_token WHERE token=?", array($token));

        $currentDateTime = date("Y-m-d H:i:s");
        $expiryDateTime = date("Y-m-d H:i:s", strtotime($tokenResult['expiry']));

        $minutesLeft = round(abs(strtotime($currentDateTime) - strtotime($expiryDateTime)) / 60, 2);

        if ($minutesLeft < 10) {
            sqlStatement("UPDATE api_token SET expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE token=?", array($token));
        }
    }
}
