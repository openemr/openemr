<?php
/**
 * AuthRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

require_once(dirname(__FILE__) . "/../../library/authentication/common_operations.php");

use OpenEMR\Common\Utils\RandomGenUtils;

class AuthRestController
{
    public function __construct()
    {
    }

    public function authenticate($authPayload)
    {
        if (strtolower(trim($authPayload["grant_type"])) !== 'password') {
            http_response_code(401);
            return;
        }

        if (empty($authPayload["username"]) || empty($authPayload["password"])) {
            http_response_code(401);
            return;
        }

        $is_valid = confirm_user_password($authPayload["username"], $authPayload["password"]);
        if (!$is_valid) {
            http_response_code(401);
            return;
        }

        if (!empty($_SESSION['api']) && !empty($_SESSION['site_id'])) {
            $encoded_api_site = bin2hex(trim($_SESSION['api']) . trim($_SESSION['site_id']));
        } else {
            http_response_code(401);
            return;
        }

        // Use base64 (except for the special characters which are + and /) in Bearer tokens
        // (note rfc6750 allows more special characters if wish to support in the future)
        $new_token = RandomGenUtils::produceRandomString(32, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
        if (empty($new_token)) {
            http_response_code(500);
            error_log("OpenEMR Error: API was unable to create a random Bearer token");
            return;
        }

        $user = sqlQuery("SELECT id FROM users_secure WHERE username = ?", array($authPayload['username']));

        $sql = " INSERT INTO api_token SET";
        $sql .= "     user_id=?,";
        $sql .= "     token=?,";
        $sql .= "     expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR)";
        sqlStatement($sql, [$user["id"], $new_token]);

        $encoded_token = $new_token . $encoded_api_site;
        $give = array("token_type" => "Bearer", "access_token" => $encoded_token, "expires_in" => "3600");
        http_response_code(200);
        return $give;
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
        if (empty($token)) {
            return false;
        }
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
