<?php

/**
 * ValidationUtils is intended for validation methods that are used in OpenEMR.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

class ValidationUtils
{
    /**
     * Returns true if the provided email is a valid email.
     *
     * @param string $email the email string to check
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return false !== filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
    }

    /**
     * Returns true if the provided credit card appears to be valid.
     * If type is provided, then the validation makes sure it is valid for that specific
     * type, otherwise it just makes sure it is valid for any type
     *
     * @todo Implement Luhn algorithm (mod-10 checksum) for proper validation (see library/js/vendors/validate/validate_extend.js)
     * @todo Update IIN patterns to modern standards (e.g., Mastercard now starts with 2221-2720, not just 51-55)
     * @todo Consider adding support for additional card types (Maestro, UnionPay, etc.)
     * @todo Validate card number length per card type specifications
     *
     * @param string $cc_num credit card number
     * @param string $type [optional] (American, Diners, Discover, Master, Visa)
     * @return bool
     */
    public static function isValidCreditCard(string $cc_num, string $type = ""): bool
    {
        if ($type === "Dinners") {
            $type = "Diners";  // typos never die, they just rest for a spell.
        }
        $patterns = [
            "American" => "/^([34|37]{2})([0-9]{13})$/", // American Express
            "Diners" => "/^([30|36|38]{2})([0-9]{12})$/", // Diner's Club
            "Discover" => "/^([6011]{4})([0-9]{12})$/", // Discover Card
            "Master" => "/^([51|52|53|54|55]{2})([0-9]{14})$/", // Mastercard
            "Visa" => "/^([4]{1})([0-9]{12,15})$/", // Visa
        ];

        // If type is specified, check only that type
        if ($type !== "") {
            return isset($patterns[$type]) && preg_match($patterns[$type], $cc_num);
        }

        // If no type specified, check all types
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cc_num)) {
                return true;
            }
        }

        return false;
    }
}
