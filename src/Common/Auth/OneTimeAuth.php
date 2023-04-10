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
     * @param array $p [
     *                 'pid' => '', // required for most onetime auth
     *                 'target_link' => '', // Onetime endpoint
     *                 'redirect_link' => '', // Where to redirect the user after auth
     *                 'enabled_datetime' => 'NOW', // Use a datetime if wish to enable for a future date.
     *                 'expiry_interval' => 'PT15M', // Always PTxx{Sec,Min,Day} PeriodTime
     *                 'email' => '']
     * @return array|bool
     * @throws \Exception
     */
    public function createPortalOneTime(array $p = []): array|bool
    {
        $redirect_token = '';
        $date_base = ($p['enabled_datetime'] ?? null) ?: 'NOW';
        $expiry = new DateTime($date_base);
        $expiry->add(new DateInterval($p['expiry_interval'] ?? 'PT15M'));
        $token_raw = RandomGenUtils::createUniqueToken(32);
        $pin = RandomGenUtils::createUniqueToken(6);
        $token_encrypt = (new CryptoGen())->encryptStandard($token_raw);
        $token_database = $token_raw . $pin . bin2hex($expiry->format('U'));
        if (!empty($p['pid']) && !empty($token_raw)) {
            $query_parameters = [$token_database, $p['pid']];
            sqlStatementNoLog("UPDATE `patient_access_onsite` SET `portal_onetime` = ? WHERE `pid` = ?", $query_parameters);
            (new SystemLogger())->debug("New onetime token added in database.");
        } else {
            (new SystemLogger())->debug("Onetime failed missing PID or token creation failed");
            return false;
        }

        if (!empty($p['target_link'] ?? null)) {
            $site_addr = trim($p['target_link']);
        } elseif ($this->context == 'portal') {
            $site_addr = trim($GLOBALS['portal_onsite_two_address']);
        }

        $rtn['encoded_link'] = $this->encodeLink($site_addr, $token_encrypt, $redirect_token);
        $rtn['onetime_token'] = $token_database;
        $rtn['redirect_token'] = $redirect_token;
        $rtn['pin'] = $pin;
        (new SystemLogger())->debug("New standard onetime token created succesfully.");

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
                }
            }
        } else {
            (new SystemLogger())->error("Onetime token invalid length.");
        }
        if ($auth === false) {
            $rtn['error'] = "Onetime creation failed " . errorLogEscape('One time auth:' . $onetime_token);
            (new SystemLogger())->error($rtn['error']);
            return $rtn;
        }
        $parse = str_replace($one_time, '', $auth['portal_onetime']);
        $validate = hex2bin(substr($parse, 6));
        if ($validate <= time()) {
            $rtn['error'] = xlt("Your one time credential reset link has expired. Reset and try again.") . "time:$validate time:" . time();
            (new SystemLogger())->error($rtn['error']);
            return $rtn;
        }
        $redirect = '';
        if (!empty($redirect_token)) {
            if ($crypto->cryptCheckStandard($redirect_token)) {
                $redirect = $crypto->decryptStandard($redirect_token, null, 'drive', 6);
                (new SystemLogger())->debug("Redirect token decrypted.");
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
     * @param $site_addr
     * @param $token_encrypt
     * @param $forward
     * @return string
     */
    private function encodeLink($site_addr, $token_encrypt, $forward = ''): string
    {
        $site_id = $_SESSION['site_id'] ?? null ?: 'default';
        if ($this->scope == 'register') {
            $encoded_link = sprintf("%s?%s", attr($site_addr), http_build_query([
                'forward_email_verify' => $token_encrypt,
                'site' => $site_id
            ]));
        } elseif ($this->scope == 'reset_password') {
            $encoded_link = sprintf("%s?%s", attr($site_addr), http_build_query([
                'forward' => $token_encrypt,
                'site' => $site_id
            ]));
        } else {
            $encoded_link = sprintf("%s?%s", attr($site_addr), http_build_query([
                'service_auth' => $token_encrypt,
                'target' => $forward,
                'site' => $site_id
            ]));
        }
        (new SystemLogger())->debug("Onetime link " . text($encoded_link) . " encoded");

        return $encoded_link;
    }
}
