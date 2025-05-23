<?php

/**
 * MfaUtils.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2020 Amiel Elboim <amielel@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

use OpenEMR\Common\Crypto\CryptoGen;
use u2flib_server\U2F;

class MfaUtils
{
    const TOTP_TOKEN_LENGTH = 6;
    const TOTP = 'TOTP';
    const U2F = 'U2F';

    private $types = array(); //type of MFA
    private $regs;
    private $registrations;
    private $var1U2F;
    private $var1TOTP;
    private $uid; // User Id who try connect
    private $errorMsg = '';
    private $appId;

    /**
     * MfaUtils constructor.
     * Load the settings of user from login_mfa_registrations
     * @param $uid - user Id
     */
    public function __construct($uid)
    {
        $this->uid = $uid;
        $res = sqlStatementNoLog(
            "SELECT a.name, a.method, a.var1 FROM login_mfa_registrations AS a " .
            "WHERE a.user_id = ? AND (a.method = 'TOTP' OR a.method = 'U2F') ORDER BY a.name",
            array($uid)
        );
        while ($row = sqlFetchArray($res)) {
            if ($row['method'] == 'U2F') {
                $this->types[] = 'U2F';
                $this->var1U2F = $row['var1'];
                $regobj = json_decode($row['var1']);
                $this->regs[json_encode($regobj->keyHandle)] = $row['name'];
                $this->registrations[] = $regobj;
            } elseif ($row['method'] == 'TOTP') {
                $this->types[] = 'TOTP';
                $this->var1TOTP = $row['var1'];
            }
        }
        $scheme = "https://"; // isset($_SERVER['HTTPS']) ? "https://" : "http://";
        $this->appId = $scheme . $_SERVER['HTTP_HOST'];
    }

    public function tokenFromRequest($type)
    {
        $token = isset($_POST['mfa_token']) ? $_POST['mfa_token'] : null;
        if (is_null($token)) {
            return null;
        }
        return $this->validateToken($token, $type) ? $token : false;
    }

    /**
     * Check if user registered to MFA
     * @return bool
     */
    public function isMfaRequired()
    {
        return !empty($this->types) ? true : false;
    }

    public function getType()
    {
        return $this->types;
    }

    /**
     * @param $token
     * Check the validity of the authentication token
     * @return bool
     * @throws \Exception
     */
    public function check($token, $type)
    {
        switch ($type) {
            case 'TOTP':
                return $this->checkTOTP($token);
                break;
            case 'U2F':
                return $this->checkU2F($token);
                break;
            default:
                throw new \Exception('MFA type do not supported');
        }
    }

    /**
     * Return the Error message
     * @return string
     */
    public function errorMessage()
    {
        return $this->errorMsg;
    }

    public function getAppId()
    {
        return $this->appId;
    }


    /**
     * Initial U2F settings
     * @return false|string
     * @throws \u2flib_server\Error
     */
    public function getU2fRequests()
    {
        $u2f = new \u2flib_server\U2F($this->appId);
        $requests =  json_encode($u2f->getAuthenticateData($this->registrations));
        sqlStatement(
            "UPDATE users_secure SET login_work_area = ? WHERE id = ?",
            array($requests, $this->uid)
        );
        return $requests;
    }

    /**
     * @param $token - token that sent in the request
     * Check code from TOTP application or device
     * @return bool
     */
    private function checkTOTP($token)
    {
        $registrationSecret = false;
        if (!empty($this->var1TOTP)) {
            $registrationSecret = $this->var1TOTP;
        }

        // Decrypt the secret
        // First, try standard method that uses standard key
        $cryptoGen = new CryptoGen();
        $secret = $cryptoGen->decryptStandard($registrationSecret);
        if (empty($secret)) {
            // Second, try the password hash, which was setup during install and is temporary
            $passwordResults = privQuery(
                "SELECT password FROM users_secure WHERE username = ?",
                array($_POST["authUser"])
            );
            if (!empty($passwordResults["password"])) {
                $secret = $cryptoGen->decryptStandard($registrationSecret, $passwordResults["password"]);
                if (!empty($secret)) {
                    error_log("Disregard the decryption failed authentication error reported above this line; it is not an error.");
                    // Re-encrypt with the more secure standard key
                    $secretEncrypt = $cryptoGen->encryptStandard($secret);
                    privStatement(
                        "UPDATE login_mfa_registrations SET var1 = ? where user_id = ? AND method = 'TOTP'",
                        array($secretEncrypt, $this->uid)
                    );
                }
            }
        }

        if (!empty($secret)) {
            $googleAuth = new \Totp($secret);
            $response = $googleAuth->validateCode($token);
        }

        if ($response) {
            return true;
        } else {
            $this->errorMsg = 'The MFA code you entered was not valid.';
            return false;
        }
    }

    /**
     * @param $token
     * Check code from U2F Key
     * @return bool
     */
    private function checkU2F($token)
    {

        $u2f = new \u2flib_server\U2F($this->appId);
        $tmprow = sqlQuery("SELECT login_work_area FROM users_secure WHERE id = ?", array($this->uid));
        try {
            $registration = $u2f->doAuthenticate(
                json_decode($tmprow['login_work_area']), // these are the original challenge requests
                $this->registrations,
                json_decode($token)
            );
            // Stored registration data needs to be updated because the usage count has changed.
            // We have to use the matching registered key.
            $strhandle = json_encode($registration->keyHandle);
            if (isset($this->regs[$strhandle])) {
                sqlStatement(
                    "UPDATE login_mfa_registrations SET `var1` = ? WHERE " .
                    "`user_id` = ? AND `method` = 'U2F' AND `name` = ?",
                    array(json_encode($registration), $this->uid, $this->regs[$strhandle])
                );
                return true;
            } else {
                error_log("Unexpected keyHandle returned from doAuthenticate(): '" . errorLogEscape($strhandle) . "'");
            }
        } catch (u2flib_server\Error $e) {
            // Authentication failed so we will build the U2F form again.
            $form_response = '';
            $this->errorMsg = xl('U2F Key Authentication error') . ": " . $e->getMessage();
            return false;
        }
    }

    /**
     * @param $token
     * check if token valid
     * @return bool
     * @throws \Exception
     */
    private function validateToken($token, $type)
    {
        switch ($type) {
            case 'TOTP':
                return strlen($token) === self::TOTP_TOKEN_LENGTH && is_numeric($token) ? true : false;
                break;
            case 'U2F':
                // todo - USF string validation
                return true;
                break;
            default:
                throw new \Exception('MFA type do not supported');
        }
    }
}
