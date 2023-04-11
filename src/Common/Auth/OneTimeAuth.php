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
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Utils\RandomGenUtils;

class OneTimeAuth
{
    private $scope;
    private $context;

    public function __construct($context = 'portal', $scope = 'redirect')
    {
        // scope = portal/service tasks (reset, register). context = portal, patient etc.
        $this->scope = $scope;
        $this->context = $context;
    }

    /**
     * @param array $p
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
    public function createPortalOneTime(array $p = []): array|bool
    {
        $redirect_token = '';
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
        $pin = substr(str_shuffle(str_shuffle("0123456789")), 0, 6);
        $token_encrypt = (new CryptoGen())->encryptStandard($token_raw);
        $token_database = $token_raw . $pin . bin2hex($expiry->format('U'));
        if (!empty($p['pid']) && !empty($token_raw)) {
            $query_parameters = [$token_database, $p['pid']];
            sqlStatementNoLog("UPDATE `patient_access_onsite` SET `portal_onetime` = ? WHERE `pid` = ?", $query_parameters);
            (new SystemLogger())->debug("New onetime token added in database.");
        } else {
            (new SystemLogger())->error("Onetime failed missing PID or token creation failed");
            return false;
        }

        $redirect_raw = trim($p['redirect_link']);
        if (!empty($redirect_raw)) {
            $redirect_plus = js_escape(['pid' => $passed_in_pid, 'to' => $redirect_raw]);
            $redirect_token = (new CryptoGen())->encryptStandard($redirect_plus);
            if (empty($redirect_token)) {
                (new SystemLogger())->error("Onetime redirect failed encryption.");
            }
        }
        if (!empty($p['target_link'] ?? null)) {
            $site_addr = trim($p['target_link']);
        } elseif ($this->context == 'portal') {
            $site_addr = trim($GLOBALS['portal_onsite_two_address']);
        }

        $rtn['encoded_link'] = $this->encodeLink($site_addr, $token_encrypt, $passed_in_pid, $redirect_token);
        $rtn['onetime_token'] = $token_database;
        $rtn['redirect_token'] = $redirect_token;
        $rtn['pin'] = $pin;
        $rtn['email'] = $email;
        (new SystemLogger())->debug("New standard onetime token created successfully.");

        return $rtn;
    }

    /**
     * @param $onetime_token
     * @param $redirect_token
     * @return array
     */
    public function decodePortalOneTime($onetime_token, $redirect_token = ''): array
    {
        $auth = false;
        $rtn = [];
        $rtn['pid'] = 0;
        $rtn['pin'] = null;
        $rtn['redirect'] = null;
        $rtn['error'] = null;
        $one_time = '';
        $crypto = new CryptoGen();
        if (strlen($onetime_token) >= 64) {
            if ($crypto->cryptCheckStandard($onetime_token)) {
                $one_time = $crypto->decryptStandard($onetime_token, null, 'drive', 6);
                if (!empty($one_time)) {
                    $auth = sqlQueryNoLog("Select * From patient_access_onsite Where portal_onetime Like BINARY ?", array($one_time . '%'));
                } else {
                    (new SystemLogger())->error("Onetime decrypt token failed. Empty!");
                }
            }
        } else {
            (new SystemLogger())->error("Onetime token invalid length.");
        }
        if (!$auth) {
            $rtn['error'] = "Onetime decode failed Onetime auth: " . $onetime_token;
            (new SystemLogger())->error($rtn['error']);
            die(xlt("Not Authorized!"));
        }
        $parse = str_replace($one_time, '', $auth['portal_onetime']);
        $validate = hex2bin(substr($parse, 6));
        if ($validate <= time()) {
            $rtn['error'] = xlt("Your one time credential reset link has expired. Reset and try again.") . "time:$validate time:" . time();
            (new SystemLogger())->error($rtn['error']);
            die(xlt("Expired. Not Authorized!"));
        }
        $redirect = '';
        if (!empty($redirect_token)) {
            if ($crypto->cryptCheckStandard($redirect_token)) {
                $redirect_decrypted = $crypto->decryptStandard($redirect_token, null, 'drive', 6);
                $redirect_array = json_decode($redirect_decrypted, true);
                $redirect = $redirect_array['to'];
                if (($redirect_array['pid'] != $auth['pid'] && !empty($redirect_array['pid']))) {
                    (new SystemLogger())->error(xlt("Error! credentials pid to and from don't match!"));
                    die(xlt("Not Authorized!"));
                }
                (new SystemLogger())->debug("Redirect token decrypted: pid= " . $redirect_array['pid'] . " redirect= " . $redirect);
            }
        }
        $rtn['pid'] = $auth['pid'];
        $rtn['pin'] = substr($parse, 0, 6);
        $rtn['redirect'] = $redirect;
        $rtn['portal_username'] = $auth['portal_username'];
        $rtn['portal_login_username'] = $auth['portal_login_username'];
        $rtn['onetime_decrypted'] = $one_time;
        (new SystemLogger())->debug("Onetime sucessfully decoded.");

        return $rtn;
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
     * @param $site_addr
     * @param $token_encrypt
     * @param $forward
     * @return string
     */
    private function encodeLink($site_addr, $token_encrypt, $pid, $forward = ''): string
    {
        $site_id = $_SESSION['site_id'] ?? null ?: 'default';
        $format = "%s&%s";
        if (stripos($site_addr, "?site") === false) {
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
            $encoded_link = sprintf($format, attr($site_addr), http_build_query([
                'id' => $pid,
                'service_auth' => $token_encrypt,
                'target' => $forward,
                'site' => $site_id
            ]));
        }
        (new SystemLogger())->debug("Onetime link " . text($encoded_link) . " encoded");

        return $encoded_link;
    }

    /**
     * @param $pid
     * @return array
     */
    public function isValidPortalPatient($pid): array
    {
        // ensure both portal and patient data match using portal account id.
        $patient = sqlQuery(
            "Select CONCAT(`fname`, `id`) As account, `pid`, `email`, `email_direct` From `patient_data` Where `pid` = ?",
            array($pid)
        );
        $portal = sqlQuery(
            "Select `pid`, `portal_username` From `patient_access_onsite` Where `portal_username` = ? And `pid` = ?",
            array($patient['account'], $patient['pid'])
        );

        $patient['valid'] = !empty($portal) && ((int)$pid === (int)$portal['pid']);

        return $patient;
    }
}
