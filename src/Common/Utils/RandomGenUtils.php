<?php

/**
 * RandomGenUtils class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

class RandomGenUtils
{
    /**
     * Produce random bytes (uses random_bytes with error checking)
     *
     * @param int $length Length of the random bytes to produce
     * @return string Random bytes as a string
     */
    public static function produceRandomBytes(int $length): string
    {
        try {
            return random_bytes($length);
        } catch (\Error $e) {
            error_log('OpenEMR Error: Encryption is not working because of random_bytes() Error: ' . errorLogEscape($e->getMessage()));
            return '';
        } catch (\Exception $e) {
            error_log('OpenEMR Error: Encryption is not working because of random_bytes() Exception: ' . errorLogEscape($e->getMessage()));
            return '';
        }
    }

    /**
     * Produce random string (uses random_int with error checking)
     *
     * @param int $length Length of the random string to produce
     * @param string $alphabet Alphabet to use for generating the random string
     * @return string Random string generated from the alphabet
     */
    public static function produceRandomString(int $length = 26, string $alphabet = 'abcdefghijklmnopqrstuvwxyz234567'): string
    {
        $str = '';
        $alphamax = strlen($alphabet) - 1;
        try {
            for ($i = 0; $i < $length; ++$i) {
                $str .= $alphabet[random_int(0, $alphamax)];
            }
            return $str;
        } catch (\Error $e) {
            error_log('OpenEMR Error: Encryption is not working because of random_int() Error: ' . errorLogEscape($e->getMessage()));
            return '';
        } catch (\Exception $e) {
            error_log('OpenEMR Error: Encryption is not working because of random_int() Exception: ' . errorLogEscape($e->getMessage()));
            return '';
        }
    }

    /**
     * Function to create a random unique token with just alphanumeric characters
     *
     * @param int $length Length of the token to create
     * @return string Random unique token
     */
    public static function createUniqueToken(int $length = 40): string
    {
        $new_token = self::produceRandomString($length, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");

        if (empty($new_token)) {
            error_log('OpenEMR Error: OpenEMR is not working because unable to create a random unique token.');
            die("OpenEMR Error: OpenEMR is not working because unable to create a random unique token.");
        }

        return $new_token;
    }

    /**
     * Function to generate a password for the patient portal
     * Randomly generates password with 12 characters that contains at least:
     * - one lower case
     * - one upper case
     * - one number
     * - one special character
     *
     * @return string Randomly generated 12-character password
     */
    public static function generatePortalPassword(): string
    {
        $max_tries = 1000; // Maximum number of tries to generate a valid password
        for ($i = 0; $i < $max_tries; $i++) {
            $the_password = self::produceRandomString(12, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%");
            if (empty($the_password)) {
                // Something is seriously wrong with the random generator
                $error_message = "OpenEMR Error: OpenEMR is not working because unable to create a random unique token.";
                error_log($error_message);
                die($error_message);
            }
            if (
                preg_match('/[A-Z]/', $the_password) &&
                preg_match('/[a-z]/', $the_password) &&
                preg_match('/[0-9]/', $the_password) &&
                preg_match('/[@#$%]/', $the_password)
            ) {
                return $the_password; // Password meets criteria
            }
        }
        // Something is seriously wrong since 1000 tries have not created a valid password
        $error_message = "OpenEMR Error: OpenEMR is not working because unable to create a valid password in $max_tries attempts.";
        error_log($error_message);
        die($error_message);
    }
}
