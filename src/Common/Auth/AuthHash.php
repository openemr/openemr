<?php

/**
 * AuthHash class.
 *
 *   Hashing:
 *     1. Hashing of passwords used for user authentication. The algorithm used for this mode can be chosen at
 *         Administration->Globals->Security->'Hash Algorithm for Authentication'.
 *     2. The passwordVerify function is static and is a wrapper for the php password_verify() function that will allow a
 *         debugging mode (Administration->Globals->Security->Debug Hash Verification Time) to measure the time it takes
 *         to verify the hash to allow fine tuning of chosen algorithm and algorithm options.
 *     3. The algorithms and algorithm options can be found in the Administration->Globals->Security settings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

use OpenEMR\Common\Utils\RandomGenUtils;

class AuthHash
{
    private $algo;          // Algorithm setting from globals
    private $algo_constant; // Standard algorithm constant, if exists

    private $options;       // Standardized array of options

    public function __construct()
    {
        $this->algo = $GLOBALS['gbl_auth_hash_algo'];

        // If SHA512HASH is selected, then ensure CRYPT_SHA512 is supported
        if ($this->algo == "SHA512HASH") {
            if (CRYPT_SHA512 != 1) {
                $this->algo == "DEFAULT";
                error_log("OpenEMR WARNING: SHA512HASH not supported, so using DEFAULT instead");
            }
        }

        // If set to php default algorithm, then figure out what it is.
        //  This basically resolves what PHP is using as PASSWORD_DEFAULT,
        //  which has been PASSWORD_BCRYPT since PHP 5.5. In future PHP versions,
        //  though, it will likely change to one of the Argon2 algorithms. And
        //  in this case, the below block of code will automatically support this
        //  transition.
        if ($this->algo == "DEFAULT") {
            if (PASSWORD_DEFAULT == PASSWORD_BCRYPT) {
                $this->algo = "BCRYPT";
            } elseif (PASSWORD_DEFAULT == PASSWORD_ARGON2I) {
                $this->algo = "ARGON2I";
            } elseif (PASSWORD_DEFAULT == PASSWORD_ARGON2ID) {
                $this->algo = "ARGON2ID";
            } elseif (PASSWORD_DEFAULT == "") {
                // In theory, should never get here, however:
                //  php 7.4 changed to using strings rather than integers for these constants
                //   and notably appears to have left PASSWORD_DEFAULT blank in several php 7.4
                //   releases rather than setting it to a default (this was fixed in php8).
                //   So, in this situation, best to default to php 7.4 default protocol
                //   (since will only get here in php 7.4), which is BCRYPT.
                $this->algo = "BCRYPT";
            } else {
                // $this->algo will stay "DEFAULT", which should never happen.
                // But if this does happen, will then not support any custom
                // options in below code since not sure what the algorithm is.
            }
        }

        // Ensure things don't break by only using a supported algorithm
        if (($this->algo == "ARGON2ID") && (!defined('PASSWORD_ARGON2ID'))) {
            // argon2id not supported, so will try argon2i instead
            $this->algo = "ARGON2I";
            error_log("OpenEMR WARNING: ARGON2ID not supported, so using ARGON2I instead");
        }
        if (($this->algo == "ARGON2I") && (!defined('PASSWORD_ARGON2I'))) {
            // argon2i not supported, so will use bcrypt instead
            $this->algo = "BCRYPT";
            error_log("OpenEMR WARNING: ARGON2I not supported, so using BCRYPT instead");
        }

        // Now can safely set up the algorithm and algorithm options
        if (($this->algo == "ARGON2ID") || ($this->algo == "ARGON2I")) {
            // Argon2
            if ($this->algo == "ARGON2ID") {
                // Using argon2ID
                $this->algo_constant = PASSWORD_ARGON2ID;
            }
            if ($this->algo == "ARGON2I") {
                // Using argon2I
                $this->algo_constant = PASSWORD_ARGON2I;
            }
            // Set up Argon2 options
            $temp_array = [];
            if (($GLOBALS['gbl_auth_argon_hash_memory_cost'] != "DEFAULT") && (check_integer($GLOBALS['gbl_auth_argon_hash_memory_cost']))) {
                $temp_array['memory_cost'] = $GLOBALS['gbl_auth_argon_hash_memory_cost'];
            }
            if (($GLOBALS['gbl_auth_argon_hash_time_cost'] != "DEFAULT") && (check_integer($GLOBALS['gbl_auth_argon_hash_time_cost']))) {
                $temp_array['time_cost'] = $GLOBALS['gbl_auth_argon_hash_time_cost'];
            }
            if (($GLOBALS['gbl_auth_argon_hash_thread_cost'] != "DEFAULT") && (check_integer($GLOBALS['gbl_auth_argon_hash_thread_cost']))) {
                $temp_array['threads'] = $GLOBALS['gbl_auth_argon_hash_thread_cost'];
            }
            if (!empty($temp_array)) {
                $this->options = $temp_array;
            }
        } elseif ($this->algo == "BCRYPT") {
            // Bcrypt - Using bcrypt and set up bcrypt options
            $this->algo_constant = PASSWORD_BCRYPT;
            if (($GLOBALS['gbl_auth_bcrypt_hash_cost'] != "DEFAULT") && (check_integer($GLOBALS['gbl_auth_bcrypt_hash_cost']))) {
                $this->options = ['cost' => $GLOBALS['gbl_auth_bcrypt_hash_cost']];
            }
        } elseif ($this->algo == "SHA512HASH") {
            // SHA512HASH - Using crypt and set up crypt option for this algo
            $this->algo_constant = $this->algo;
            if (check_integer($GLOBALS['gbl_auth_sha512_rounds'])) {
                $this->options = ['rounds' => $GLOBALS['gbl_auth_sha512_rounds']];
            } else {
                $this->options = ['rounds' => 100000];
            }
        } else {
            // This should never happen.
            //  Will only happen if unable to map the DEFAULT setting above or if using a invalid setting other than
            //   BCRYPT, ARGON2I, or ARGON2ID.
            // If this happens, then will just go with PHP Default (ie. go with default php algorithm and options).
            $this->algo_constant = PASSWORD_DEFAULT;
            error_log("OpenEMR WARNING: Unable to resolve hashing preference, so using PHP Default");
        }
    }

    public function passwordHash(&$password)
    {
        // Process SHA512HASH algo separately, since uses crypt
        if ($this->algo == "SHA512HASH") {
            // Create salt
            $salt = RandomGenUtils::produceRandomString(16, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");

            // Create hash
            return crypt($password, '$6$rounds=' . $this->options['rounds'] . '$' . $salt . '$');
        }

        // Process algos supported by standard password_hash
        if (empty($this->options)) {
            return password_hash($password, $this->algo_constant);
        } else {
            return password_hash($password, $this->algo_constant, $this->options);
        }
    }

    public function passwordNeedsRehash($hash)
    {
        if ($this->algo == "SHA512HASH") {
            // Process when going to SHA512HASH algo separately, since not supported by standard password_needs_rehash
            if (empty(preg_match('/^\$6\$rounds=/', $hash))) {
                // algo does not match, so needs rehash
                return true;
            }
            preg_match('/^\$6\$rounds=([0-9]*)\$/', $hash, $matches);
            $rounds = $matches[1];
            if ($rounds != $this->options['rounds']) {
                // number of rounds does not match, so needs rehash
                return true;
            }
        } elseif (!empty(preg_match('/^\$6\$rounds=/', $hash))) {
            // Process when going from SHA512HASH algo separately, since not supported by standard password_needs_rehash
            // Note we already know that $this->algo != "SHA512HASH", so we return true
            return true;
        } else {
            // Process when going to and from algos supported by standard password_needs_rehash
            if (empty($this->options)) {
                return password_needs_rehash($hash, $this->algo_constant);
            } else {
                return password_needs_rehash($hash, $this->algo_constant, $this->options);
            }
        }
    }

    // To improve performance, this function is run as static since
    //  requires no defines from the class. The goal of this wrapper is
    //  to provide the execution timing debugging feature to allow
    //  tuning of the hashing (can turn the debugging feature on
    //  at Administration->Globals->Security->Debug Hash Verification Time).
    public static function passwordVerify(&$password, $hash): bool
    {
        if (empty($password) || empty($hash)) {
            error_log("OpenEMR Error: call to passwordVerify is missing password or hash");
            return false;
        }

        if ($GLOBALS['gbl_debug_hash_verify_execution_time']) {
            // Reporting collection time to allow fine tuning of hashing algorithm
            $millisecondsStart = round(microtime(true) * 1000);
        }

        if (!empty(preg_match('/^\$6\$rounds=/', $hash))) {
            // Process SHA512HASH algo separately, since uses crypt
            $valid = hash_equals($hash, crypt($password, $hash));
        } else {
            // Process algos supported by standard password_verify
            $valid = password_verify($password, $hash);

            if (!$valid) {
                // Ensure do not need to process legacy hash (pre 5.0.0), which will get converted to standard hash
                //  after a successful auth. This legacy hash was created with a salt of 21 characters rather than the standard
                //  22 characters. Because of this, it does not work with above password_verify. Need to derive the salt
                //  from the hash (up to the period character 29 in the hash). Note that this will not work on some
                //  operating systems (for example, alpine linux crypt will return an error * instead of the hash because the
                //  salt is not the correct length).
                //
                //  TODO: Consider removing this at some time in the future (early 2022) since it overcomplicates authorization.
                //
                if (!empty(preg_match('/^\$2a\$05\$/', $hash)) && (substr($hash, 28, 1) === '.')) {
                    $fixedSalt = substr($hash, 0, 28) . "$";
                    if (strlen($fixedSalt) !== 29) {
                        return false;
                    } else {
                        $valid = hash_equals($hash, crypt($password, $fixedSalt));
                    }
                }
            }
        }

        if ($GLOBALS['gbl_debug_hash_verify_execution_time']) {
            // Reporting collection time to allow fine tuning of hashing algorithm
            $millisecondsStop = round(microtime(true) * 1000);
            error_log("Password hash verification execution time was following (milliseconds): " . errorLogEscape($millisecondsStop - $millisecondsStart));
        }

        return $valid;
    }

    // To improve performance, this function is run as static since
    //  requires no defines from the class
    public static function hashValid($hash)
    {
        //  (note need to preg_match for \$2a\$05\$ for backward compatibility since
        //   password_get_info() call can not identify older bcrypt hashes)
        //  (note also need to preg_match for /^\$6\$rounds=/ to support the SHA512HASH hashing option)
        $hash_info = password_get_info($hash);
        if (empty($hash_info['algo']) && empty(preg_match('/^\$2a\$05\$/', $hash)) && empty(preg_match('/^\$6\$rounds=/', $hash))) {
            // Invalid hash
            return false;
        } else {
            // Valid hash
            return true;
        }
    }
}
