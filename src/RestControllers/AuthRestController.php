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

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
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

        if (empty(trim($_SESSION['api'])) || empty(trim($_SESSION['site_id']))) {
            http_response_code(401);
            return;
        }

        if (empty($authPayload["username"]) || empty($authPayload["password"])) {
            http_response_code(401);
            return;
        }

        $ip = collectIpAddresses();

        $userCheck = sqlQueryNoLog("select `active` from `users` where BINARY `username` = ?", [$authPayload['username']]);
        if (empty($userCheck) || $userCheck['active'] != 1) {
            EventAuditLogger::instance()->newEvent('api', $authPayload['username'], '', 0, "API failure: " . $ip['ip_string'] . ". user not active or not found in users table");
            http_response_code(401);
            return;
        }

        $authGroupCheck = sqlQueryNoLog("select `name` from `groups` where BINARY `user` = ?", [$authPayload['username']]);
        if (empty($authGroupCheck) || empty($authGroupCheck['name'])) {
            EventAuditLogger::instance()->newEvent('api', $authPayload['username'], '', 0, "API failure: " . $ip['ip_string'] . ". user not found in a group");
            http_response_code(401);
            return;
        }

        $user = sqlQueryNoLog("SELECT `id` FROM `users_secure` WHERE BINARY `username` = ?", [$authPayload['username']]);
        if (empty($user) || empty($user['id'])) {
            EventAuditLogger::instance()->newEvent('api', $authPayload['username'], $authGroupCheck['name'], 0, "API failure: " . $ip['ip_string'] . ". user not found in auth table");
            http_response_code(401);
            return;
        }

        $is_valid = confirm_user_password($authPayload["username"], $authPayload["password"]);
        if (!$is_valid) {
            EventAuditLogger::instance()->newEvent('api', $authPayload["username"], $authGroupCheck['name'], 0, "API failure: " . $ip['ip_string'] . ". user password incorrect");
            http_response_code(401);
            return;
        }

        // Bearer token creation
        // Goal is to mitigate both brute force and pass the hash attacks
        //  -Brute force is mitigated by encrypting/signing the token that is sent to user
        //  -Pass the hash is mitigated by storing the second half of the token in the database as only a secure hash
        // Create a token of 64 in 2 halves
        //  -The first half is stored in database in plain text (ensure is unique) for querying
        //  -The second half is stored in database as secure hash for authentication
        //  -Both halves are combined and sent back to user in encrypted/signed form that is included in a json structure
        //   that also includes the site_id and api type (and the entire json structure is encoded in base64). This will
        //   be the Bearer token.
        $isUnique = false;
        $i = 0;
        while (!$isUnique) {
            $new_token_a = RandomGenUtils::produceRandomString(32, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
            $new_token_b = RandomGenUtils::produceRandomString(32, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
            $new_token_b_salt = oemr_password_salt();
            $new_token_b_hash = oemr_password_hash($new_token_b, $new_token_b_salt);
            if (empty($new_token_a) || empty($new_token_b) || empty($new_token_b_salt) || empty($new_token_b_hash)) {
                http_response_code(500);
                error_log("OpenEMR Error: API was unable to create a random Bearer token and/or hash");
                return;
            }
            $i++;
            if ($i > 1000) {
                http_response_code(500);
                error_log("OpenEMR Error: API was unable to create a unique Bearer token");
                return;
            }
            // purposefully not using binary here to ensure unique even when binary mistakenly not used elsewhere
            $checkUnique = sqlQueryNoLog("SELECT * FROM `api_token` WHERE `token`=?", [$new_token_a]);
            if (empty($checkUnique)) {
                $isUnique = true;
            }
        }

        // Storing token_a as plain text and token_b as secure hash
        $sql = " INSERT INTO api_token SET";
        $sql .= "     user_id=?,";
        $sql .= "     token=?,";
        $sql .= "     token_auth_salt=?,";
        $sql .= "     token_auth=?,";
        $sql .= "     expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR)";
        sqlStatementNoLog($sql, [$user["id"], $new_token_a, $new_token_b_salt, $new_token_b_hash]);

        // Sending the full token back to user in encrypted/signed form
        $cryptoGen = new CryptoGen();
        $encrypted_new_full_token = $cryptoGen->encryptStandard($new_token_a . $new_token_b);
        if (empty($encrypted_new_full_token)) {
            http_response_code(500);
            error_log("OpenEMR Error: API was unable to create a encrypted and signed token");
            return;
        }
        $encoded_token = base64_encode(json_encode(['token' => $encrypted_new_full_token, 'site_id' => trim($_SESSION['site_id']), 'api' => trim($_SESSION['api'])]));
        $give = array("token_type" => "Bearer", "access_token" => $encoded_token, "expires_in" => "3600");
        EventAuditLogger::instance()->newEvent('api', $authPayload["username"], $authGroupCheck['name'], 1, "API success for API token request: " . $ip['ip_string']);
        http_response_code(200);
        return $give;
    }

    public function isValidToken($tokenRaw)
    {
        // Token validation
        // Goal is to mitigate both brute force and pass the hash attacks
        //  -Brute force is mitigated by decrypting/validating the encrypted/signed token
        //  -Pass the hash is mitigated by checking a secure hash of second half of token with secure hash stored in database

        // decrypt/validate the encrypted/signed token
        $token = $this->decryptValidateToken($tokenRaw);
        if (empty($token)) {
            return false;
        }

        // Divide the token of 64 in 2 separate parts
        //  The first part is stored in database in plain text (ensured was unique) for querying
        //  The second part is stored in database as secure hash for authentication
        $token_a = substr($token, 0, 32);
        $token_b = substr($token, 32);
        if (empty($token_a) || empty($token_b)) {
            return false;
        }

        $ip = collectIpAddresses();

        // Query with first part of token
        $tokenResult = sqlQueryNoLog("SELECT u.username, a.token, a.token_auth_salt, a.token_auth, a.expiry FROM api_token a JOIN users_secure u ON u.id = a.user_id WHERE BINARY token=?", array($token_a));
        if (!$tokenResult || empty($tokenResult['username']) || empty($tokenResult['token']) || empty($tokenResult['token_auth_salt']) || empty($tokenResult['token_auth']) || empty($tokenResult['expiry'])) {
            EventAuditLogger::instance()->newEvent('api', $tokenResult['username'], '', 0, "API failure: " . $ip['ip_string'] . ". token not found");
            return false;
        }

        // Authenticate with second part of token
        if (!hash_equals(oemr_password_hash($token_b, $tokenResult['token_auth_salt']), $tokenResult['token_auth'])) {
            EventAuditLogger::instance()->newEvent('api', $tokenResult['username'], '', 0, "API failure: " . $ip['ip_string'] . ". token not authorized");
            return false;
        }

        // Ensure token not expired
        $currentDateTime = date("Y-m-d H:i:s");
        $expiryDateTime = date("Y-m-d H:i:s", strtotime($tokenResult['expiry']));
        if ($expiryDateTime <= $currentDateTime) {
            EventAuditLogger::instance()->newEvent('api', $tokenResult['username'], '', 0, "API failure: " . $ip['ip_string'] . ". token expired");
            return false;
        }

        EventAuditLogger::instance()->newEvent('api', $tokenResult['username'], '', 1, "API success for API token use: " . $ip['ip_string']);
        return true;
    }

    public function aclCheck($tokenRaw, $section, $value)
    {
        // decrypt/validate the encrypted/signed token
        $token = $this->decryptValidateToken($tokenRaw);
        if (empty($token)) {
            return false;
        }

        // Only use first part of token since authentication of token not needed here
        $token = substr($token, 0, 32);

        // Collect username
        $sql = " SELECT";
        $sql .= " u.username";
        $sql .= " FROM api_token a";
        $sql .= " JOIN users_secure u ON u.id = a.user_id";
        $sql .= " WHERE BINARY a.token = ?";
        $userResult = sqlQueryNoLog($sql, array($token));
        if (empty($userResult["username"])) {
            return false;
        }

        // Check if user is authorized
        return acl_check($section, $value, $userResult["username"]);
    }

    public function optionallyAddMoreTokenTime($tokenRaw)
    {
        // decrypt/validate the encrypted/signed token
        $token = $this->decryptValidateToken($tokenRaw);
        if (empty($token)) {
            return false;
        }

        // Only use first part of token since authentication of token not needed here
        $token = substr($token, 0, 32);

        $tokenResult = sqlQueryNoLog("SELECT `expiry` FROM `api_token` WHERE BINARY `token`=?", [$token]);
        if (empty($tokenResult) || empty($tokenResult['expiry'])) {
            return false;
        }

        $currentDateTime = date("Y-m-d H:i:s");
        $expiryDateTime = date("Y-m-d H:i:s", strtotime($tokenResult['expiry']));

        $minutesLeft = round(abs(strtotime($currentDateTime) - strtotime($expiryDateTime)) / 60, 2);

        if ($minutesLeft < 10) {
            sqlStatementNoLog("UPDATE api_token SET expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE BINARY token=?", array($token));
        }
    }

    private function decryptValidateToken($token)
    {
        // decrypt/validate the encrypted/signed token (also ensure the decrypted token is 64 characters)
        if (empty($token)) {
            return false;
        }
        $cryptoGen = new CryptoGen();
        if (!$cryptoGen->cryptCheckStandard($token)) {
            return false;
        }
        $decrypt_token = $cryptoGen->decryptStandard($token, null, 'drive', 6);
        if (empty($decrypt_token)) {
            return false;
        }
        if (strlen($decrypt_token) != 64) {
            return false;
        }
        return $decrypt_token;
    }
}
