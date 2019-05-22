<?php
/**
 * RandomGenUtils class.
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
    // Produce random bytes (uses random_bytes with error checking)
    public static function produceRandomBytes($length)
    {
        try {
            $randomBytes = random_bytes($length);
        } catch (Error $e) {
            error_log('OpenEMR Error : Encryption is not working because of random_bytes() Error: ' . errorLogEscape($e->getMessage()));
            return '';
        } catch (Exception $e) {
            error_log('OpenEMR Error : Encryption is not working because of random_bytes() Exception: ' . errorLogEscape($e->getMessage()));
            return '';
        }

        return $randomBytes;
    }

    // Produce random string (uses random_int with error checking)
    public static function produceRandomString($length = 26, $alphabet = 'abcdefghijklmnopqrstuvwxyz234567')
    {
        $str = '';
        $alphamax = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; ++$i) {
            try {
                $str .= $alphabet[random_int(0, $alphamax)];
            } catch (Error $e) {
                error_log('OpenEMR Error : Encryption is not working because of random_int() Error: ' . errorLogEscape($e->getMessage()));
                return '';
            } catch (Exception $e) {
                error_log('OpenEMR Error : Encryption is not working because of random_int() Exception: ' . errorLogEscape($e->getMessage()));
                return '';
            }
        }
        return $str;
    }

    // Function to create a random unique token with just alphanumeric characters
    public static function createUniqueToken($length = 40)
    {
        $new_token = self::produceRandomString($length, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");

        if (empty($new_token)) {
            error_log('OpenEMR Error : OpenEMR is not working because unable to create a random unique token.');
            die("OpenEMR Error : OpenEMR is not working because unable to create a random unique token.");
        }

        return $new_token;
    }
}
