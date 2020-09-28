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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Auth\AuthUtils;
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

        if (($_SESSION['api'] == "port") || ($_SESSION['api'] == "pofh")) {
            // authentication for patient portal api
            if (empty($authPayload["email"])) {
                $authPayload["email"] = '';
            }
            $authAPILogin = new AuthUtils('portal-api');
            if (!$authAPILogin->confirmPassword($authPayload["username"], $authPayload["password"], $authPayload["email"])) {
                http_response_code(401);
                return;
            }
            $userId = 0;
            $userGroup = '';
            $patientId = $authAPILogin->getPatientId();
            if (empty($patientId)) {
                // Something is seriously wrong
                error_log('OpenEMR Error : OpenEMR is not working because unable to collect critical information.');
                die("OpenEMR Error : OpenEMR is not working because unable to collect critical information.");
            }
            $event = 'portalapi';
            $genericUserId = $patientId;
            $_SESSION['pid'] = $patientId;
        } else { // $_SESSION['api'] == "oemr" || $_SESSION['api'] == "fhir"
            // authentication for core api or fhir
            $authAPILogin = new AuthUtils('api');
            if (!$authAPILogin->confirmPassword($authPayload["username"], $authPayload["password"])) {
                http_response_code(401);
                return;
            }
            $userId = $authAPILogin->getUserId();
            $userGroup = $authAPILogin->getUserGroup();
            $patientId = 0;
            if (empty($userId) || empty($userGroup)) {
                // Something is seriously wrong
                error_log('OpenEMR Error : OpenEMR is not working because unable to collect critical information.');
                die("OpenEMR Error : OpenEMR is not working because unable to collect critical information.");
            }
            $event = 'api';
            $genericUserId = $userId;
            $_SESSION['authUser'] = $authPayload["username"];
            $_SESSION['authUserID'] = $userId;
            $_SESSION['authProvider'] = $userGroup;
        }

        // PASSED
        //
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
        $authHashToken = new AuthHash('token');
        while (!$isUnique) {
            $new_token_a = RandomGenUtils::produceRandomString(32, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
            $new_token_b = RandomGenUtils::produceRandomString(32, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
            $new_token_b_hash = $authHashToken->passwordHash($new_token_b);
            if (empty($new_token_b_hash)) {
                // Something is seriously wrong
                error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
                die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
            }
            if (empty($new_token_a) || empty($new_token_b)) {
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
        $sql .= "     `token_api` = ?,";
        $sql .= "     `user_id` = ?,";
        $sql .= "     `patient_id` = ?,";
        $sql .= "     `token` = ?,";
        $sql .= "     `token_auth` = ?,";
        $sql .= "     `expiry` = DATE_ADD(NOW(), INTERVAL 1 HOUR)";
        sqlStatementNoLog($sql, [$_SESSION['api'], $userId, $patientId, $new_token_a, $new_token_b_hash]);

        // Sending the full token back to user in encrypted/signed form
        $cryptoGen = new CryptoGen();
        $encrypted_new_full_token = $cryptoGen->encryptStandard($new_token_a . $new_token_b);
        if (empty($encrypted_new_full_token)) {
            http_response_code(500);
            error_log("OpenEMR Error: API was unable to create a encrypted and signed token");
            return;
        }
        $encoded_token = base64_encode(json_encode(['token' => $encrypted_new_full_token, 'site_id' => trim($_SESSION['site_id']), 'api' => trim($_SESSION['api'])]));
        $give = array("token_type" => "Bearer", "access_token" => $encoded_token, "expires_in" => "3600", "user_data" => array("user_id" => $genericUserId));
        $ip = collectIpAddresses();
        EventAuditLogger::instance()->newEvent($event, $authPayload["username"], $userGroup, 1, "API success for API token request: " . $ip['ip_string'], !empty($patientId) ? $patientId : null);
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
        if (($_SESSION['api'] == "port") || ($_SESSION['api'] == "pofh")) {
            // For patient portal api
            $tokenResult = sqlQueryNoLog(
                "SELECT pd.`uuid`, p.`pid`, p.`portal_username`, p.`portal_login_username`, a.`token`, a.`token_auth`, a.`expiry`
                FROM `api_token` a
                JOIN `patient_access_onsite` p ON p.`pid` = a.`patient_id`
                JOIN `patient_data` pd ON pd.`pid` = a.`patient_id`
                WHERE BINARY a.`token` = ? AND a.`token_api` = ?",
                array($token_a, $_SESSION['api'])
            );
            if (!$tokenResult || empty($tokenResult['uuid']) || empty($tokenResult['pid']) || empty($tokenResult['portal_username']) || empty($tokenResult['portal_login_username']) || empty($tokenResult['token']) || empty($tokenResult['token_auth']) || empty($tokenResult['expiry'])) {
                EventAuditLogger::instance()->newEvent('portalapi', $tokenResult['portal_login_username'], '', 0, "API failure: " . $ip['ip_string'] . ". token not found", $tokenResult['pid']);
                return false;
            }
            $event = 'portalapi';
            $genericUserName = $tokenResult['portal_login_username'];
            $logPid = $tokenResult['pid'];
        } else { // $_SESSION['api'] == "oemr" || $_SESSION['api'] == "fhir"
            // For core api or fhir api
            $tokenResult = sqlQueryNoLog(
                "SELECT u.`username`, a.`user_id`, a.`token`, a.`token_auth`, a.`expiry`
                FROM `api_token` a
                JOIN `users_secure` u ON u.`id` = a.`user_id`
                WHERE BINARY a.`token` = ? AND a.`token_api` = ?",
                array($token_a, $_SESSION['api'])
            );
            if (!$tokenResult || empty($tokenResult['username']) || empty($tokenResult['user_id']) || empty($tokenResult['token']) || empty($tokenResult['token_auth']) || empty($tokenResult['expiry'])) {
                EventAuditLogger::instance()->newEvent('api', $tokenResult['username'], '', 0, "API failure: " . $ip['ip_string'] . ". token not found");
                return false;
            }
            $event = 'api';
            $genericUserName = $tokenResult['username'];
            $logPid = null;
        }

        // Authenticate with second part of token
        if (AuthHash::passwordVerify($token_b, $tokenResult['token_auth'])) {
            $authHashToken = new AuthHash('token');
            if ($authHashToken->passwordNeedsRehash($tokenResult['token_auth'])) {
                // If so, create a new hash, and replace the old one (this will ensure always using most modern hashing)
                $newHash = $authHashToken->passwordHash($token_b);
                if (empty($newHash)) {
                    // Something is seriously wrong
                    error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
                    die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
                }
                sqlStatementNoLog("UPDATE `api_token` SET `token_auth` = ? WHERE BINARY `token` = ?", [$newHash, $token_a]);
            }
        } else {
            EventAuditLogger::instance()->newEvent($event, $genericUserName, '', 0, "API failure: " . $ip['ip_string'] . ". token not authorized", $logPid);
            return false;
        }

        // Ensure token not expired
        $currentDateTime = date("Y-m-d H:i:s");
        $expiryDateTime = date("Y-m-d H:i:s", strtotime($tokenResult['expiry']));
        if ($expiryDateTime <= $currentDateTime) {
            EventAuditLogger::instance()->newEvent($event, $genericUserName, '', 0, "API failure: " . $ip['ip_string'] . ". token expired", $logPid);
            return false;
        }

        // SUCCESS
        EventAuditLogger::instance()->newEvent($event, $genericUserName, '', 1, "API success for API token use: " . $ip['ip_string'], $logPid);

        // Maintenance - remove all tokens that have been expired for more than a day
        $currentDateTimePlusDay = date("Y-m-d H:i:s", strtotime('-1 day'));
        sqlStatementNoLog("DELETE FROM `api_token` WHERE `expiry` < ?", [$currentDateTimePlusDay]);

        // Set needed session variables
        if (($_SESSION['api'] == "port") || ($_SESSION['api'] == "pofh")) {
            // For patient portal api
            $_SESSION['pid'] = $tokenResult['pid'];
            $_SESSION['puuid'] = $tokenResult['uuid'];
        } else { // $_SESSION['api'] == "oemr" || $_SESSION['api'] == "fhir"
            // For core api or fhir api
            $_SESSION['authUser'] = $tokenResult['username'];
            $_SESSION['authUserID'] = $tokenResult['user_id'];
            $_SESSION['authProvider'] =  sqlQueryNoLog("SELECT `name` FROM `groups` WHERE `user` = ?", [$_SESSION['authUser']])['name'];
        }

        return true;
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
