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
    public static function isValidEmail($email)
    {
        // FILTER_FLAG_EMAIL_UNICODE allows for unicode characters in the local (part before the @) of the email
        if (filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            // TODO: OpenEMR has used this validator regex for 11+ years... leaving this line in case we need to revert
            // on January 30th 2023 added the ability to support SMTP label addresses such as myname+label@gmail.com
            // Fixes #6159 (openemr/openemr/issues/6159)

//        if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-\+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email)) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the provided credit card appears to be valid.
     * If type is provided, then the validation makes sure it is valid for that specific
     * type, otherwise it just makes sure it is valid for any type
     *
     * @param string $cc_num credit card number
     * @param string $type [optional] (American, Dinners, Discover, Master, Visa)
     * @return bool
     */
    public static function isValidCreditCard($cc_num, $type = "")
    {
        if ($type == "American") {
            $denum = "American Express";
        } elseif ($type == "Dinners") {
            $denum = "Diner's Club";
        } elseif ($type == "Discover") {
            $denum = "Discover";
        } elseif ($type == "Master") {
            $denum = "Master Card";
        } elseif ($type == "Visa") {
            $denum = "Visa";
        }

        $verified = false;

        if ($type == "American" || $type == "") {
            $pattern = "/^([34|37]{2})([0-9]{13})$/"; // American Express
            if (preg_match($pattern, $cc_num)) {
                $verified = true;
            }
        }

        if ($type == "Dinners" || $type == "") {
            $pattern = "/^([30|36|38]{2})([0-9]{12})$/"; // Diner's Club
            if (preg_match($pattern, $cc_num)) {
                $verified = true;
            }
        }

        if ($type == "Discover" || $type == "") {
            $pattern = "/^([6011]{4})([0-9]{12})$/"; // Discover Card
            if (preg_match($pattern, $cc_num)) {
                $verified = true;
            }
        }

        if ($type == "Master" || $type == "") {
            $pattern = "/^([51|52|53|54|55]{2})([0-9]{14})$/"; // Mastercard
            if (preg_match($pattern, $cc_num)) {
                $verified = true;
            }
        }

        if ($type == "Visa" || $type == "") {
            $pattern = "/^([4]{1})([0-9]{12,15})$/"; // Visa
            if (preg_match($pattern, $cc_num)) {
                $verified = true;
            }
        }

        return $verified;
    }
}
