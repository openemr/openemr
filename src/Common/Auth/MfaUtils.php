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

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\KeyVersion;
use OpenEMR\Common\Crypto\PasswordBasedCrypto;
use OpenEMR\Common\Database\QueryUtils;

class MfaUtils
{
    const TOTP_TOKEN_LENGTH = 6;
    const TOTP = 'TOTP';
    const U2F = 'U2F';

    private $types = []; //type of MFA
    private $regs;
    private $registrations;
    private $var1U2F;
    private $var1TOTP;
    private ?string $nameTOTP = null;
    private $errorMsg = '';
    private $appId;

    /**
     * MfaUtils constructor.
     * Load the settings of user from login_mfa_registrations
     * @param $uid User Id who try connect
     */
    public function __construct(private $uid)
    {
        $res = sqlStatementNoLog(
            "SELECT a.name, a.method, a.var1 FROM login_mfa_registrations AS a " .
            "WHERE a.user_id = ? AND (a.method = 'TOTP' OR a.method = 'U2F') ORDER BY a.name",
            [$this->uid]
        );
        while ($row = sqlFetchArray($res)) {
            if ($row['method'] == 'U2F') {
                $this->types[] = 'U2F';
                $this->var1U2F = $row['var1'];
                $regobj = json_decode((string) $row['var1']);
                $this->regs[json_encode($regobj->keyHandle)] = $row['name'];
                $this->registrations[] = $regobj;
            } elseif ($row['method'] == 'TOTP') {
                $this->types[] = 'TOTP';
                $this->var1TOTP = $row['var1'];
                // Save the row's name so the atomic-consumption UPDATE
                // in checkTOTP can target exactly this registration.
                // The (user_id, name) primary key does not enforce
                // one TOTP row per user, so we must pin the update
                // to the row whose secret we're validating.
                $this->nameTOTP = is_string($row['name']) ? $row['name'] : null;
            }
        }
        $scheme = "https://"; // isset($_SERVER['HTTPS']) ? "https://" : "http://";
        $this->appId = $scheme . $_SERVER['HTTP_HOST'];
    }

    public function tokenFromRequest($type)
    {
        $token = $_POST['mfa_token'] ?? null;
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
        return !empty($this->types);
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
        return match ($type) {
            'TOTP' => $this->checkTOTP($token),
            'U2F' => $this->checkU2F($token),
            default => throw new \Exception('MFA type do not supported'),
        };
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
            [$requests, $this->uid]
        );
        return $requests;
    }

    /**
     * @param $token - token that sent in the request
     * Check code from TOTP application or device
     * @return bool
     */
    private function checkTOTP($token): bool
    {
        // Refuse further attempts if this user or IP has already
        // exceeded the standard lockout threshold on MFA challenges.
        // Prevents attackers from continuing to grind codes against
        // an already-blocked counter. The dedicated mfa_fail_counter
        // / mfa_login_fail_counter are separate from the password
        // counters so an in-progress MFA brute force is not zeroed
        // out by the password-verify-success reset that happens on
        // every login attempt.
        $ip = collectIpAddresses();
        $callerIp = $ip['ip_string'];
        // Resolve the username from the uid the constructor loaded MFA
        // rows for, not from $_POST. The web login form posts 'authUser'
        // but the OAuth2 password grant posts 'username' — pulling from
        // the request would leave the per-user counter unbumped on the
        // password-grant path. The uid is authoritative for either
        // caller.
        $userRow = QueryUtils::querySingleRow(
            "SELECT `username` FROM `users_secure` WHERE `id` = ?",
            [$this->uid]
        );
        $authUser = is_array($userRow) && is_string($userRow['username'] ?? null)
            ? $userRow['username']
            : null;
        $authUtils = new AuthUtils();
        if ($authUtils->isMfaChallengeBlocked($authUser, $callerIp)) {
            $this->errorMsg = 'The MFA code you entered was not valid.';
            return false;
        }

        $registrationSecret = false;
        if (!empty($this->var1TOTP)) {
            $registrationSecret = $this->var1TOTP;
        }

        // Decrypt the secret
        // First, try standard method that uses standard key
        $cryptoGen = ServiceContainer::getCrypto();
        try {
            $secret = $cryptoGen->decryptFromDatabase(is_string($registrationSecret) ? $registrationSecret : null);
        } catch (CryptoGenException) {
            $secret = null;
        }
        if (empty($secret)) {
            // Second, try the password hash, which was setup during install and is temporary
            $passwordResults = privQuery(
                "SELECT password FROM users_secure WHERE username = ?",
                [$_POST["authUser"]]
            );
            if (!empty($passwordResults["password"])) {
                $passwordCrypto = new PasswordBasedCrypto(KeyVersion::CURRENT);
                try {
                    $secret = $passwordCrypto->decrypt((string) $registrationSecret, (string) $passwordResults["password"]);
                } catch (\OpenEMR\Common\Crypto\CryptoGenException) {
                    $secret = null;
                }
                if (!empty($secret)) {
                    error_log("Disregard the decryption failed authentication error reported above this line; it is not an error.");
                    // Re-encrypt with the more secure standard key. Pin the
                    // update to the specific registration row that owned the
                    // legacy-encrypted secret — the schema's composite
                    // (user_id, name) key allows multiple TOTP rows per
                    // user, so a bare user_id + method match would overwrite
                    // sibling rows with the wrong secret.
                    $secretEncrypt = $cryptoGen->encryptForDatabase($secret);
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE login_mfa_registrations SET var1 = ? "
                            . "WHERE user_id = ? AND method = 'TOTP' AND name = ?",
                        [$secretEncrypt, $this->uid, $this->nameTOTP],
                        noLog: true
                    );
                }
            }
        }

        $matchedSlice = 0;
        if (!empty($secret)) {
            $googleAuth = new \Totp($secret);
            $matchedSlice = $googleAuth->validateCodeAndGetSlice($token);
        }

        if ($matchedSlice > 0) {
            // Atomic single-use consumption via slice-monotonic replay
            // check (RFC 6238's recommended defense). RobThree's
            // verifyCode accepts codes for slices {T-1, T, T+1} with
            // the default discrepancy, so up to 3 different valid
            // codes can coexist within a ~90-second window. Storing
            // only the last token wouldn't catch an A-B-A replay
            // (consume A, then B, then A again while A is still in
            // the acceptance window). Storing the matched slice and
            // requiring the incoming slice be STRICTLY GREATER blocks
            // that entire class of replay.
            //
            // The UPDATE is conditional on the monotonicity predicate
            // and we require affectedRows === 1, so two concurrent
            // requests race for exactly one winner rather than both
            // passing a separate pre-check.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `login_mfa_registrations` "
                    . "SET `last_used_step` = ?, `last_challenge` = NOW() "
                    . "WHERE `user_id` = ? AND `method` = 'TOTP' AND `name` = ? "
                    . "AND (`last_used_step` IS NULL OR `last_used_step` < ?)",
                [$matchedSlice, $this->uid, $this->nameTOTP, $matchedSlice],
                noLog: true
            );
            if (QueryUtils::affectedRows() !== 1) {
                // Either a concurrent request already consumed this
                // slice, or the incoming code came from an earlier
                // slice than the last consumed one (A-B-A replay).
                $authUtils->recordFailedMfaChallenge($authUser);
                $this->errorMsg = 'The MFA code you entered was not valid.';
                return false;
            }
            return true;
        } else {
            $authUtils->recordFailedMfaChallenge($authUser);
            $this->errorMsg = 'The MFA code you entered was not valid.';
            return false;
        }
    }

    /**
     * @param $token
     * Check code from U2F Key
     * @return bool
     */
    private function checkU2F($token): bool
    {

        $u2f = new \u2flib_server\U2F($this->appId);
        $tmprow = sqlQuery("SELECT login_work_area FROM users_secure WHERE id = ?", [$this->uid]);
        try {
            $registration = $u2f->doAuthenticate(
                json_decode((string) $tmprow['login_work_area']), // these are the original challenge requests
                $this->registrations,
                json_decode((string) $token)
            );
            // Stored registration data needs to be updated because the usage count has changed.
            // We have to use the matching registered key.
            $strhandle = json_encode($registration->keyHandle);
            if (isset($this->regs[$strhandle])) {
                sqlStatement(
                    "UPDATE login_mfa_registrations SET `var1` = ? WHERE " .
                    "`user_id` = ? AND `method` = 'U2F' AND `name` = ?",
                    [json_encode($registration), $this->uid, $this->regs[$strhandle]]
                );
                return true;
            } else {
                error_log("Unexpected keyHandle returned from doAuthenticate(): '" . errorLogEscape($strhandle) . "'");
                return false;
            }
        } catch (\u2flib_server\Error $e) {
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
        return match ($type) {
            'TOTP' => strlen((string) $token) === self::TOTP_TOKEN_LENGTH && is_numeric($token),
            // todo - USF string validation
            'U2F' => true,
            default => throw new \Exception('MFA type do not supported'),
        };
    }
}
