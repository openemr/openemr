<?php

/**
 * This is a compatibility file for password_hash and password_verify for
 * php systems prior to 5.5 that do not have password hashing built-in.
 * This will export these two functions to the global namespace
 */

defined('PASSWORD_BCRYPT') or define('PASSWORD_BCRYPT', 1);

defined('PASSWORD_DEFAULT') or define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);

if (! function_exists('password_hash')) {
    /**
     * Hash the password using the specified algorithm
     *
     * @param string $password
     *          The password to hash
     * @param int $algo
     *          The algorithm to use (Defined by PASSWORD_* constants)
     * @param array $options
     *          The options for the algorithm to use
     *
     * @return s string|false The hashed password, or false on error.
     */
    function password_hash($password, $algo, $options = array())
    {
        if (! function_exists('crypt')) {
            trigger_error("Crypt must be loaded for password_hash to function", E_USER_WARNING);
            return null;
        }

        if (! is_string($password)) {
            trigger_error("password_hash(): Password must be a string", E_USER_WARNING);
            return null;
        }

        if (! is_int($algo)) {
            trigger_error("password_hash() expects parameter 2 to be long, " . gettype($algo) . " given", E_USER_WARNING);
            return null;
        }

        switch ($algo) {
            case PASSWORD_BCRYPT:
                // Note that this is a C constant, but not exposed to PHP, so we don't define it here.
                $cost = 10;
                if (isset($options ['cost'])) {
                    $cost = $options ['cost'];
                    if ($cost < 4 || $cost > 31) {
                        trigger_error(sprintf("password_hash(): Invalid bcrypt cost parameter specified: %d", $cost), E_USER_WARNING);
                        return null;
                    }
                }

                $required_salt_len = 22;
                $hash_format = sprintf("$2y$%02d$", $cost);
                break;
            default:
                trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
                return null;
        }

        if (isset($options ['salt'])) {
            switch (gettype($options ['salt'])) {
                case 'NULL':
                case 'boolean':
                case 'integer':
                case 'double':
                case 'string':
                    $salt = (string) $options ['salt'];
                    break;
                case 'object':
                    if (method_exists($options ['salt'], '__tostring')) {
                        $salt = (string) $options ['salt'];
                        break;
                    }

                    //NOTE FALL-THROUGH CASE HERE. POSSIBLE BUG.
                case 'array':
                case 'resource':
                default:
                    trigger_error('password_hash(): Non-string salt parameter supplied', E_USER_WARNING);
                    return null;
            }

            if (strlen($salt) < $required_salt_len) {
                trigger_error(sprintf("password_hash(): Provided salt is too short: %d expecting %d", strlen($salt), $required_salt_len), E_USER_WARNING);
                return null;
            } elseif (0 == preg_match('#^[a-zA-Z0-9./]+$#D', $salt)) {
                $salt = str_replace('+', '.', base64_encode($salt));
            }
        } else {
            $salt = __password_make_salt($required_salt_len);
        }

        $salt = substr($salt, 0, $required_salt_len);

        $hash = $hash_format . $salt;

        $ret = crypt($password, $hash);

        if (! is_string($ret) || strlen($ret) < 13) {
            return false;
        }

        return $ret;
    }
}

if (! function_exists('password_get_info')) {
    /**
     * Get information about the password hash.
     * Returns an array of the information
     * that was used to generate the password hash.
     *
     * array(
     * 'algo' => 1,
     * 'algoName' => 'bcrypt',
     * 'options' => array(
     * 'cost' => 10,
     * ),
     * )
     *
     * @param string $hash
     *          The password hash to extract info from
     *
     * @return array The array of information about the hash.
     */
    function password_get_info($hash)
    {
        $return = array (
                'algo' => 0,
                'algoName' => 'unknown',
                'options' => array ()
        );
        if (substr($hash, 0, 4) == '$2y$' && strlen($hash) == 60) {
            $return ['algo'] = PASSWORD_BCRYPT;
            $return ['algoName'] = 'bcrypt';
            list ( $cost ) = sscanf($hash, "$2y$%d$");
            $return ['options'] ['cost'] = $cost;
        }

        return $return;
    }
}

if (! function_exists('password_needs_rehash')) {
    /**
     * Determine if the password hash needs to be rehashed according to the options provided
     *
     * If the answer is true, after validating the password using password_verify, rehash it.
     *
     * @param string $hash
     *          The hash to test
     * @param int $algo
     *          The algorithm used for new password hashes
     * @param array $options
     *          The options array passed to password_hash
     *
     * @return boolean True if the password needs to be rehashed.
     */
    function password_needs_rehash($hash, $algo, array $options = array())
    {
        $info = password_get_info($hash);
        if ($info ['algo'] != $algo) {
            return true;
        }

        switch ($algo) {
            case PASSWORD_BCRYPT:
                $cost = isset($options ['cost']) ? $options ['cost'] : 10;
                if ($cost != $info ['options'] ['cost']) {
                    return true;
                }
                break;
        }

        return false;
    }
}

if (! function_exists('password_verify')) {
    /**
     * Verify a password against a hash using a timing attack resistant approach
     *
     * @param string $password
     *          The password to verify
     * @param string $hash
     *          The hash to verify against
     *
     * @return boolean If the password matches the hash
     */
    function password_verify($password, $hash)
    {
        if (! function_exists('crypt')) {
            trigger_error("Crypt must be loaded for password_create to function", E_USER_WARNING);
            return false;
        }

        $ret = crypt($password, $hash);
        if (! is_string($ret) || strlen($ret) != strlen($hash)) {
            return false;
        }

        $status = 0;
        for ($i = 0; $i < strlen($ret); $i++) {
            $status |= (ord($ret [$i]) ^ ord($hash [$i]));
        }

        return $status === 0;
    }
}

/**
 * Function to make a salt
 *
 * DO NOT USE THIS FUNCTION DIRECTLY
 *
 * @internal
 *
 */
function __password_make_salt($length)
{
    if ($length <= 0) {
        trigger_error(sprintf("Length cannot be less than or equal zero: %d", $length), E_USER_WARNING);
        return false;
    }

    $buffer = '';
    $raw_length = (int) ($length * 3 / 4 + 1);
    $buffer_valid = false;
    if (function_exists('mcrypt_create_iv')) {
        $buffer = mcrypt_create_iv($raw_length, MCRYPT_DEV_URANDOM);
        if ($buffer) {
            $buffer_valid = true;
        }
    }

    if (! $buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
        $buffer = openssl_random_pseudo_bytes($raw_length);
        if ($buffer) {
            $buffer_valid = true;
        }
    }

    if (! $buffer_valid && file_exists('/dev/urandom')) {
        $f = @fopen('/dev/urandom', 'r');
        if ($f) {
            $read = strlen($buffer);
            while ($read < $raw_length) {
                $buffer .= fread($f, $raw_length - $read);
                $read = strlen($buffer);
            }

            fclose($f);
            if ($read >= $raw_length) {
                $buffer_valid = true;
            }
        }
    }

    if (! $buffer_valid) {
        for ($i = 0; $i < $raw_length; $i++) {
            $buffer .= chr(mt_rand(0, 255));
        }
    }

    $buffer = str_replace('+', '.', base64_encode($buffer));
    return substr($buffer, 0, $length);
}
