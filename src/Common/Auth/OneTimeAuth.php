<?php

/**
 * OneTimeAuth Class
 * Service for Onetime token creation, routing and auth
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

use DateInterval;
use DateTime;
use MyMailer;
use OpenEMR\Common\Auth\Exception\OneTimeAuthException;
use OpenEMR\Common\Auth\Exception\OneTimeAuthExpiredException;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;

class OneTimeAuth
{
    private $scope;
    private $context;
    private $cryptoGen;
    private $systemLogger;

    public function __construct($context = 'portal', $scope = 'redirect')
    {
        // scope = portal/service tasks (reset, register). context = portal, patient etc.
        $this->scope = $scope;
        $this->context = $context;
        $this->cryptoGen = new CryptoGen();
        $this->systemLogger = new SystemLogger();
    }

    /**
     * @param array $p
     * @param bool  $encrypt_redirect
     * @return array|bool
     * @throws \Exception
     *
     *   $p[
     *    'pid' => '', // required for most onetime auth
     *   'target_link' => '', // Onetime endpoint
     *   'redirect_link' => '', // Where to redirect the user after auth
     *   'enabled_datetime' => 'NOW', // Use a datetime if wish to enable for a future date.
     *   'expiry_interval' => 'PT15M', // Always PTxx{Sec,Min,Day} PeriodTime
     *   'email' => '']
     */
    public function createPortalOneTime(array $p, bool $encrypt_redirect = false): array|bool
    {
        $redirect_token = null;
        $passed_in_pid = $p['pid'] ?? 0;
        $valid = $this->isValidPortalPatient($passed_in_pid);
        if (empty($valid['valid'] ?? null) || empty($passed_in_pid)) {
            throw new \RuntimeException(xlt("Invalid Pid or patient not found!"));
        }
        $email = ($valid['email'] ?? '') ?: ($p['email'] ?? '');
        $date_base = ($p['enabled_datetime'] ?? null) ?: 'NOW';
        $expiry = new DateTime($date_base);
        $expiry->add(new DateInterval($p['expiry_interval'] ?? 'PT15M'));
        $token_raw = RandomGenUtils::createUniqueToken(32);
        $token_encrypt = $this->cryptoGen->encryptStandard($token_raw);
        $pin = substr(str_shuffle(str_shuffle("0123456789")), 0, 6);
        if (empty($p['pid']) || empty($token_raw)) {
            $err = xlt("Onetime failed missing PID or token creation failed");
            $this->systemLogger->error($err);
            throw new \RuntimeException($err);
        }

        $redirect_raw = trim($p['redirect_link'] ?? null);
        if (!empty($redirect_raw) && $encrypt_redirect) {
            $redirect_plus = js_escape(['pid' => $passed_in_pid, 'to' => $redirect_raw]);
            $redirect_token = $this->cryptoGen->encryptStandard($redirect_plus);
            if (empty($redirect_token)) {
                // since redirect should be in database we can continue.
                $this->systemLogger->error(xlt("Onetime redirect failed encryption."));
            }
        }
        if (!empty($p['target_link'] ?? null)) {
            $site_addr = trim($p['target_link']);
        } elseif ($this->context == 'portal') {
            $site_addr = trim($GLOBALS['portal_onsite_two_address']);
        } else {
            $err = xlt("Onetime creation failed. Missing site address!");
            $this->systemLogger->error($err);
            throw new \RuntimeException($err);
        }

        $rtn['encoded_link'] = $this->encodeLink($site_addr, $token_encrypt, $redirect_token);
        $rtn['onetime_token'] = $token_encrypt;
        $rtn['redirect_token'] = $redirect_token;
        $rtn['pin'] = $pin;
        $rtn['email'] = $email;

        $save = $this->insertOnetime($passed_in_pid, $pin, $token_raw, $redirect_raw, $expiry->format('U'));
        if (empty($save)) {
            $err = xlt("Onetime save failed!");
            $this->systemLogger->error($err);
            throw new \RuntimeException($err);
        }

        $this->systemLogger->debug(xlt("New standard onetime token created and saved successfully."));

        return $rtn;
    }

    /**
     * @param $onetime_token
     * @param $redirect_token
     * @return array
     * @throws OneTimeAuthExpiredException
     */
    public function decodePortalOneTime($onetime_token, $redirect_token = null): array
    {
        $auth = false;
        $rtn = [];
        $rtn['pid'] = 0;
        $rtn['pin'] = null;
        $rtn['redirect'] = null;
        $rtn['error'] = null;
        $one_time = '';

        if (strlen($onetime_token) >= 64) {
            if ($this->cryptoGen->cryptCheckStandard($onetime_token)) {
                $one_time = $this->cryptoGen->decryptStandard($onetime_token, null, 'drive', 6);
                if (!empty($one_time)) {
                    $t_info = $this->getOnetime($one_time);
                    if (!empty($t_info['pid'] ?? 0)) {
                        $auth = sqlQueryNoLog("Select * From patient_access_onsite Where `pid` = ?", array($t_info['pid']));
                    }
                } else {
                    $this->systemLogger->error("Onetime decrypt token failed. Empty!");
                }
            }
        } else {
            $this->systemLogger->error("Onetime token invalid length.");
        }
        if (!$auth) {
            $rtn['error'] = "Onetime decode failed Onetime auth: " . $onetime_token;
            $this->systemLogger->error($rtn['error']);
            throw new OneTimeAuthException($rtn['error']);
        }

        $validate = $t_info['expires'];
        if ($validate <= time()) {
            $rtn['error'] = xlt("Your one time credential reset link has expired. Reset and try again.") . "time:$validate time:" . time();
            $this->systemLogger->error($rtn['error']);
            throw new OneTimeAuthExpiredException($rtn['error'], $auth['pid']);
        }
        // We'll rely on the stored redirect address as default but
        // leave the option of using embedded encrypted redirect.
        $redirect = $t_info['redirect_url'] ?? null;
        if (!empty($redirect_token)) {
            if ($this->cryptoGen->cryptCheckStandard($redirect_token)) {
                $redirect_decrypted = $this->cryptoGen->decryptStandard($redirect_token, null, 'drive', 6);
                $redirect_array = json_decode($redirect_decrypted, true);
                $redirect = $redirect_array['to'];
                if (($redirect_array['pid'] != $auth['pid'] && !empty($redirect_array['pid']))) {
                    throw new OneTimeAuthException(xlt("Error! credentials pid to and from don't match!"), $auth['pid']);
                }
                $this->systemLogger->debug("Redirect token decrypted: pid = " . $redirect_array['pid'] . " redirect = " . $redirect);
            }
        }
        $rtn['pid'] = $auth['pid'];
        $rtn['pin'] = $t_info['onetime_pin'];
        $rtn['redirect'] = $redirect;
        $rtn['username'] = $auth['portal_username'];
        $rtn['login_username'] = $auth['portal_login_username'];
        $rtn['portal_pwd'] = $auth['portal_pwd'];
        $rtn['onetime_decrypted'] = $one_time;

        $this->updateOnetime($auth['pid'], $one_time);
        $this->systemLogger->debug("Onetime successfully decoded. $one_time");

        return $rtn;
    }

    /**
     * @param $site_addr
     * @param $token_encrypt
     * @param $encrypted_redirect
     * @return string
     */
    private function encodeLink($site_addr, $token_encrypt, $encrypted_redirect = null): string
    {
        $site_id = ($_SESSION['site_id'] ?? null) ?: 'default';
        if (stripos($site_addr, "portal") !== false) {
            $site_addr = strtok($site_addr, '?');
            if (stripos($site_addr, "index.php") !== false) {
                $site_addr = dirname($site_addr);
            }
            if (substr($site_addr, -1) == '/') {
                $site_addr = substr($site_addr, 0, -1);
            }
        }
        $format = "%s&%s";
        if (stripos($site_addr, "?") === false) {
            $format = "%s?%s";
        }
        if ($this->scope == 'register') {
            $encoded_link = sprintf($format, attr($site_addr), http_build_query([
                'forward_email_verify' => $token_encrypt,
                'site' => $site_id
            ]));
        } elseif ($this->scope == 'reset_password') {
            $encoded_link = sprintf($format, attr($site_addr), http_build_query([
                'forward' => $token_encrypt,
                'site' => $site_id
            ]));
        } else {
            if (!empty($encrypted_redirect)) {
                $encoded_link = sprintf($format, attr($site_addr), http_build_query([
                    'service_auth' => $token_encrypt,
                    'target' => $encrypted_redirect,
                    'site' => $site_id
                ]));
            } else {
                $encoded_link = sprintf($format, attr($site_addr), http_build_query([
                    'service_auth' => $token_encrypt,
                    'site' => $site_id
                ]));
            }
        }
        $this->systemLogger->debug("Onetime link " . text($encoded_link) . " encoded");

        return $encoded_link;
    }

    /**
     * Credit to Stephen Neilson
     *
     * @param $email
     * @return bool
     */
    private function isValidEmail($email): bool
    {
        if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-\+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email)) {
            return true;
        }
        return false;
    }

    /**
     * @param       $email
     * @param       $body
     * @param array $user
     * @return string
     */
    public function emailNotification($email, $body, array $user = ['fname' => 'Portal', 'lname' => 'Administration']): string
    {
        $from_name = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        $mail = new MyMailer();
        $from_name = text($from_name);
        $from = $GLOBALS["practice_return_email_path"];
        $mail->AddReplyTo($from, $from_name);
        $mail->SetFrom($from, $from);
        $to = $email;
        $to_name = $email;
        $mail->AddAddress($to, $to_name);
        $subject = xlt("Session Request");
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        if ($mail->Send()) {
            $status = xlt("Email successfully sent.");
        } else {
            $status = xlt("Error: Email failed") . text($mail->ErrorInfo);
        }
        return $status;
    }

    /**
     * @param $pid
     * @return array
     */
    public function isValidPortalPatient($pid): array
    {
        // ensure both portal and patient data match using portal account id.
        $patient = sqlQuery(
            "Select `pid`, `email`, `email_direct` From `patient_data` Where `pid` = ?",
            array($pid)
        );
        $portal = sqlQuery(
            "Select `pid`, `portal_username` From `patient_access_onsite` Where `pid` = ?",
            array($patient['pid'])
        );

        $patient['valid'] = !empty($portal['portal_username']) && ((int)$pid === (int)$portal['pid']);

        return $patient;
    }

    /**
     * @param $pid
     * @param $onetime_pin
     * @param $onetime_token
     * @param $redirect_url
     * @param $expires
     * @return int
     */
    public function insertOnetime($pid, $onetime_pin, $onetime_token, $redirect_url, $expires): int
    {
        $bind = [$pid, $_SESSION['authUserID'] ?? null, $this->context, $onetime_pin, $onetime_token, $redirect_url, $expires];

        $sql = "INSERT INTO `onetime_auth` (`id`, `pid`, `create_user_id`, `context`, `onetime_pin`, `onetime_token`, `redirect_url`, `expires`, `date_created`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, current_timestamp())";

        return sqlInsert($sql, $bind);
    }

    /**
     * @param $pid
     * @param $token
     * @param $ip
     * @return bool|array|null
     */
    public function updateOnetime($pid, $token, $ip = null): bool|array|null
    {
        $access_ip = $ip ?: $_SERVER['REMOTE_ADDR'] ?? null;
        $sql = "UPDATE `onetime_auth` SET `remote_ip` = ?, `last_accessed` = current_timestamp(), `access_count` = `access_count`+1 WHERE `pid` = ? AND `onetime_token` = ?";

        return sqlQuery($sql, array($access_ip, $pid, $token));
    }

    /**
     * @param $token
     * @param $pid
     * @return bool|array|null
     */
    public function getOnetime($token, $pid = null): bool|array|null
    {
        $sql = "SELECT * FROM `onetime_auth` WHERE `onetime_token` Like BINARY ? LIMIT 1";
        $bind = [$token];
        if ($pid) {
            $bind = [$pid, $token];
            $sql = "SELECT * FROM `onetime_auth` WHERE `pid` = ? AND `onetime_token` = ? LIMIT 1";
        }

        return sqlQuery($sql, $bind);
    }

    /**
     * @param $token
     * @param $redirect_token
     * @return array
     */
    public function processOnetime($token, $redirect_token): array
    {
        $auth = $this->decodePortalOneTime($token, $redirect_token);
        if (!empty($auth['error'] ?? null)) {
            $this->systemLogger->error("Failed " . $auth['error']);
            unset($auth);
            throw new OneTimeAuthException(xlt("Authentication Failed! Contact administrator."));
        }
        $patientService = new PatientService();
        $patient = $patientService->findByPid($auth['pid']);

        // preserve session for target use
        $_SESSION['pid'] = $auth['pid'];
        $_SESSION['auth_pin'] = $auth['pin'];
        $_SESSION['auth_scope'] = $this->scope;
        $_SESSION['redirect_target'] = $auth['redirect'];
        $_SESSION['onetime'] = $auth['portal_pwd'];
        $_SESSION['patient_portal_onsite_two'] = 1;

        // set up the other variables needed for the session interaction
        // this was taken from portal/get_patient_info.php
        $userService = new UserService();
        $tmp = $userService->getUser($patient['providerID']);
        $_SESSION['providerName'] = ($tmp['fname'] ?? '') . ' ' . ($tmp['lname'] ?? '');
        $_SESSION['providerUName'] = $tmp['username'] ?? null;
        $_SESSION['sessionUser'] = '-patient-';
        $_SESSION['providerId'] = $patient['providerID'] ? $patient['providerID'] : 'undefined';
        $_SESSION['ptName'] = $patient['fname'] . ' ' . $patient['lname'];
        // never set authUserID though authUser is used for ACL!
        $_SESSION['authUser'] = 'portal-user';
        $_SESSION['portal_username'] = $auth['username']; // required by portal/handle_note.php
        $_SESSION['portal_login_username'] = $auth['login_username']; // required by portal/handle_note.php
        // Set up the csrf private_key (for the patient portal)
        //  Note this key always remains private and never leaves server session. It is used to create
        //  the csrf tokens.
        CsrfUtils::setupCsrfKey();
        // $auth['redirect'] .= "&me=" . session_id();
        header('Location: ' . $auth['redirect']);
        // allows logging and any other processing to be handled on the return
        return $auth;
    }
}
