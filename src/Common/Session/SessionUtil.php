<?php

/**
 * start/destroy session/cookie for OpenEMR or OpenEMR patient-portal or OpenEMR oauth2
 *
 * Note that keeping this class self-sufficient since it is used before the class autoloader
 *  in scripts that need to support both the core OpenEMR and patient portal.
 *
 * OpenEMR session/cookie strategy:
 *  1. The vital difference between the OpenEMR and OpenEMR patient-portal/oauth2 session/cookie is the
 *     cookie_httponly setting.
 *     a. For core OpenEMR, need to set cookie_httponly to false, since javascript needs to be able to
 *        access/modify the cookie to support separate logins in OpenEMR. This is important
 *        to support in OpenEMR since the application needs to robustly support access of
 *        separate patients via separate logins by same users. This is done via custom
 *        restore_session() javascript function; session IDs are effectively saved in the
 *        top level browser window.
 *     b. For (patient) portal OpenEMR and oauth2, setting cookie_httponly to true. Since only one patient will
 *        be logging into the patient portal, can set this to true, which will help to prevent XSS
 *        vulnerabilities.
 *  2. Set the cookie_samesite to Strict in in order to prevent csrf vulnerabilities.
 *     Exception to this is the oauth2 session which requires Lax to allow a get request from
 *     outside origin to function correctly. Note the Strict setting also is set in core
 *     OpenEMR restoreSession() javascript function so it is maintained when the session id
 *     is changed in the cookie (also is used in the transmit_form() function in login.php
 *     and standardSessionCookieDestroy() function to avoid browser warnings).
 *  3. Using use_strict_mode, use_cookies, and use_only_cookies to optimize security.
 *  4. Using sid_bits_per_character of 6 to optimize security. This does allow comma to
 *     be used in the session id, so need to ensure properly escape it when modify it in
 *     cookie. Note that this setting is not used in PHP 8.4 and higher since has been deprecated and is forced to
 *     be set to 4 as of this writing (guessing PHP may increase over time to maintain security standards).
 *  5. Using sid_length of 48 to optimize security. Note that this setting is not used in PHP 8.4 and higher since
 *     has been deprecated and is forced to be set to 32 as of this writing (guessing PHP will increase over time to
 *     maintain security standards).
 *  6. Setting gc_maxlifetime to 14400 since defaults for session.gc_maxlifetime is
 *     often too small.
 *  7. For core OpenEMR and oauth2, setting cookie_path to improve security when using different OpenEMR instances
 *     on same server to prevent session conflicts.
 *  8. Centralize session/cookie destroy.
 *  9. Session locking. To prevent session locking, which markedly decreases performance in core OpenEMR
 *     there are 3 functions for setting and unsetting session variables. These allow
 *     running OpenEMR core without session lock (by not allowing writing to session) unless need to
 *     write to session (it will then re-open the session for this). In OpenEMR core, the general strategy
 *     is to use the standard php session locking on code that works on critical session variables during
 *     authorization related scripts and in cases of single process use (such as with command line scripts
 *     and non-local api calls) since there is no performance benefit in single process use.
 *  10. For OpenEMR 6.0.0 added a oauth2 session, which requires following settings:
 *      cookie_samesite = None (In theory, should just need Lax (since just GET requests), however, need None for Smart Apps used
 *                              within OpenEMR to work)
 *      cookie_secure = true (issuer needs to be https, so makes sense to support this setting; also need since
 *                            cookie_samesite is set to None)
 *  11. For OpenEMR 6.0.0 added a api session, which requires following settings:
 *      cookie_secure = true (oauth needs to be https, so makes sense to support this setting)
 *
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

class SessionUtil
{
    private const CORE_SESSION_ID = "OpenEMR";
    private const OAUTH_SESSION_ID = 'authserverOpenEMR';

    private static $gc_maxlifetime = 14400;
    private static $use_strict_mode = true;
    private static $use_cookies = true;
    private static $use_only_cookies = true;
    private static $use_cookie_samesite = "Strict";
    private static $use_cookie_httponly = true;
    private static $use_cookie_secure = false;

    // Following setting have been deprecated in PHP 8.4 and higher
    // (ie. will remove them when PHP 8.4 is the minimum requirement)
    private static $sid_bits_per_character = 6;
    private static $sid_length = 48;

    public static function switchToCoreSession($web_root, $read_only = true): void
    {
        session_write_close();
        session_id($_COOKIE[self::CORE_SESSION_ID] ?? '');
        self::coreSessionStart($web_root, $read_only);
    }

    public static function coreSessionStart($web_root, $read_only = true): void
    {
        // Note there is no system logger here since that class does not
        //  yet exist in this context.
        $settings = [
            'read_and_close' => $read_only,
            'cookie_samesite' => self::$use_cookie_samesite,
            'cookie_secure' => self::$use_cookie_secure,
            'name' => self::CORE_SESSION_ID,
            'cookie_httponly' => false,
            'cookie_path' => ((!empty($web_root)) ? $web_root . '/' : '/'),
            'gc_maxlifetime' => self::$gc_maxlifetime,
            'use_strict_mode' => self::$use_strict_mode,
            'use_cookies' => self::$use_cookies,
            'use_only_cookies' => self::$use_only_cookies
        ];

        // PHP 8.4 and higher does not support sid_bits_per_character and sid_length
        // (ie. will remove below code block when PHP 8.4 is the minimum requirement)
        if (version_compare(phpversion(), '8.4.0', '<')) {
            // Code to run on PHP < 8.4
            $settings = array_merge([
                'sid_bits_per_character' => self::$sid_bits_per_character,
                'sid_length' => self::$sid_length
            ], $settings);
        }

        session_start($settings);
    }

    public static function setSession($session_key_or_array, $session_value = null): void
    {
        // Since our default is read_and_close the session shouldn't be active here.
        if (session_status() === PHP_SESSION_ACTIVE) {
            // ensure the session file is written from a previous
            // session open for write.
            session_write_close();
        }
        self::coreSessionStart($GLOBALS['webroot'], false);
        if (is_array($session_key_or_array)) {
            foreach ($session_key_or_array as $key => $value) {
                $_SESSION[$key] = $value;
            }
        } else {
            $_SESSION[$session_key_or_array] = $session_value;
        }
        session_write_close();
    }

    public static function unsetSession($session_key_or_array): void
    {
        self::coreSessionStart($GLOBALS['webroot'], false);
        if (is_array($session_key_or_array)) {
            foreach ($session_key_or_array as $value) {
                unset($_SESSION[$value]);
            }
        } else {
            unset($_SESSION[$session_key_or_array]);
        }
        session_write_close();
    }

    public static function setUnsetSession($setArray, $unsetArray): void
    {
        self::coreSessionStart($GLOBALS['webroot'], false);
        foreach ($setArray as $key => $value) {
            $_SESSION[$key] = $value;
        }
        foreach ($unsetArray as $value) {
            unset($_SESSION[$value]);
        }
        session_write_close();
    }

    public static function coreSessionDestroy(): void
    {
        self::standardSessionCookieDestroy();
    }

    public static function portalSessionStart(): void
    {
        // Note there is no system logger here since that class does not
        //  yet exist in this context.
        $settings = [
            'cookie_samesite' => self::$use_cookie_samesite,
            'cookie_secure' => self::$use_cookie_secure,
            'name' => 'PortalOpenEMR',
            'cookie_httponly' => self::$use_cookie_httponly,
            'gc_maxlifetime' => self::$gc_maxlifetime,
            'use_strict_mode' => self::$use_strict_mode,
            'use_cookies' => self::$use_cookies,
            'use_only_cookies' => self::$use_only_cookies
        ];

        // PHP 8.4 and higher does not support sid_bits_per_character and sid_length
        // (ie. will remove below code block when PHP 8.4 is the minimum requirement)
        if (version_compare(phpversion(), '8.4.0', '<')) {
            // Code to run on PHP < 8.4
            $settings = array_merge([
                'sid_bits_per_character' => self::$sid_bits_per_character,
                'sid_length' => self::$sid_length
            ], $settings);
        }

        session_start($settings);
    }

    public static function portalSessionCookieDestroy(): void
    {
        // Note there is no system logger here since that class does not
        //  yet exist in this context.
        self::standardSessionCookieDestroy();
    }

    public static function apiSessionStart($web_root): void
    {
        $settings = [
            'cookie_samesite' => self::$use_cookie_samesite,
            'cookie_secure' => true,
            'name' => 'apiOpenEMR',
            'cookie_httponly' => self::$use_cookie_httponly,
            'cookie_path' => ((!empty($web_root)) ? $web_root . '/apis/' : '/apis/'),
            'gc_maxlifetime' => self::$gc_maxlifetime,
            'use_strict_mode' => self::$use_strict_mode,
            'use_cookies' => self::$use_cookies,
            'use_only_cookies' => self::$use_only_cookies
        ];

        // PHP 8.4 and higher does not support sid_bits_per_character and sid_length
        // (ie. will remove below code block when PHP 8.4 is the minimum requirement)
        if (version_compare(phpversion(), '8.4.0', '<')) {
            // Code to run on PHP < 8.4
            $settings = array_merge([
                'sid_bits_per_character' => self::$sid_bits_per_character,
                'sid_length' => self::$sid_length
            ], $settings);
        }

        session_start($settings);
    }

    public static function apiSessionCookieDestroy(): void
    {
        self::standardSessionCookieDestroy();
    }

    public static function switchToOAuthSession($web_root): void
    {
        session_write_close();
        session_id($_COOKIE[self::OAUTH_SESSION_ID] ?? '');
        self::oauthSessionStart($web_root);
    }

    public static function oauthSessionStart($web_root): void
    {
        $settings = [
            'cookie_samesite' => "None",
            'cookie_secure' => true,
            'name' => self::OAUTH_SESSION_ID,
            'cookie_httponly' => self::$use_cookie_httponly,
            'cookie_path' => ((!empty($web_root)) ? $web_root . '/oauth2/' : '/oauth2/'),
            'gc_maxlifetime' => self::$gc_maxlifetime,
            'use_strict_mode' => self::$use_strict_mode,
            'use_cookies' => self::$use_cookies,
            'use_only_cookies' => self::$use_only_cookies
        ];

        // PHP 8.4 and higher does not support sid_bits_per_character and sid_length
        // (ie. will remove below code block when PHP 8.4 is the minimum requirement)
        if (version_compare(phpversion(), '8.4.0', '<')) {
            // Code to run on PHP < 8.4
            $settings = array_merge([
                'sid_bits_per_character' => self::$sid_bits_per_character,
                'sid_length' => self::$sid_length
            ], $settings);
        }

        session_start($settings);
    }

    public static function oauthSessionCookieDestroy(): void
    {
        self::standardSessionCookieDestroy();
    }

    public static function setupScriptSessionStart(): void
    {
        $settings = [
            'cookie_samesite' => self::$use_cookie_samesite,
            'cookie_secure' => self::$use_cookie_secure,
            'name' => 'setupOpenEMR',
            'cookie_httponly' => self::$use_cookie_httponly,
            'gc_maxlifetime' => self::$gc_maxlifetime,
            'use_strict_mode' => self::$use_strict_mode,
            'use_cookies' => self::$use_cookies,
            'use_only_cookies' => self::$use_only_cookies
        ];

        // PHP 8.4 and higher does not support sid_bits_per_character and sid_length
        // (ie. will remove below code block when PHP 8.4 is the minimum requirement)
        if (version_compare(phpversion(), '8.4.0', '<')) {
            // Code to run on PHP < 8.4
            $settings = array_merge([
                'sid_bits_per_character' => self::$sid_bits_per_character,
                'sid_length' => self::$sid_length
            ], $settings);
        }

        session_start($settings);
    }

    public static function setupScriptSessionCookieDestroy(): void
    {
        self::standardSessionCookieDestroy();
    }

    private static function standardSessionCookieDestroy(): void
    {
        // Destroy the cookie
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $params["path"],
                'domain' => $params["domain"],
                'secure' => $params["secure"],
                'httponly' => $params["httponly"],
                'samesite' => $params["samesite"]
            ]
        );

        // Destroy the session.
        session_destroy();
    }
}
