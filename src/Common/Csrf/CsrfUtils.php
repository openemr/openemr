<?php

/**
 * CsrfUtils class.
 *
 *   OpenEMR CSRF prevention strategy:
 *    1. A secret key is created upon login that is stored in session.
 *    2. This key is used to build CSRF tokens via hash_hmac().
 *    3. This mechanism allows creation of infinite CSRF tokens from 1 secret key.
 *    4. Currently creating separate tokens for 'api' and 'default'.
 *    5. Note we are truncating the hash_hmac() hash to 40 characters (rather than
 *       the full 64 of a sha256 hash) so as not to break character limits when
 *       CSRF tokens are used in GET requests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Csrf;

use OpenEMR\Common\Utils\RandomGenUtils;

class CsrfUtils
{
    // Function to create a private csrf key and store as a session variable
    //  Note this key always remains private and never leaves server session. It is used to create
    //  the csrf tokens.
    public static function setupCsrfKey()
    {
        $_SESSION['csrf_private_key'] = RandomGenUtils::produceRandomBytes(32);
        if (empty($_SESSION['csrf_private_key'])) {
            error_log("OpenEMR Error : OpenEMR is potentially not secure because unable to create the CSRF key.");
        }
    }

    // Function to collect the csrf token
    //  Limiting token size to 40 (truncated 64(sha256 hash size) to 40) to avoid long GET requests
    //  $subject allows creation of different csrf tokens:
    //    Using 'api' for the internal api csrf token
    //    Using 'default' for everything else (for now)
    public static function collectCsrfToken($subject = 'default')
    {
        if (empty($_SESSION['csrf_private_key'])) {
            error_log("OpenEMR Error : OpenEMR is potentially not secure because CSRF key is empty.");
            return false;
        }
        return substr(hash_hmac('sha256', $subject, $_SESSION['csrf_private_key']), 0, 40);
    }

    // Function to verify a csrf_token
    public static function verifyCsrfToken($token, $subject = 'default')
    {
        $currentToken = self::collectCsrfToken($subject);

        if (empty($currentToken)) {
            error_log("OpenEMR Error : OpenEMR is potentially not secure because CSRF token was not formed correctly.");
            return false;
        } elseif (empty($token)) {
            return false;
        } elseif (hash_equals($currentToken, $token)) {
            return true;
        } else {
            return false;
        }
    }

    // Function to manage when a csrf token is not verified
    public static function csrfNotVerified($toScreen = true, $toLog = true, $die = true)
    {
        if ($toScreen) {
            echo xlt('Authentication Error');
        }
        if ($toLog) {
            error_log("OpenEMR CSRF token authentication error");
        }
        if ($die) {
            die;
        }
    }
}
