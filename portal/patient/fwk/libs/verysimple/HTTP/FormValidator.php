<?php

/**
 * Static utility class for validating form input
 *
 * Contains various methods for validating standard information such
 * as email, dates, credit card numbers, etc
 *
 * @package verysimple::HTTP
 * @author VerySimple Inc.
 * @copyright 1997-2008 VerySimple, Inc. http://www.verysimple.com
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */

/**
 * import supporting libraries
 */

use OpenEMR\Common\Utils\ValidationUtils;

/**
 * Static utility class for validating form input
 *
 * Contains various methods for validating standard information such
 * as email, dates, credit card numbers, etc
 *
 * @package verysimple::HTTP
 * @author VerySimple Inc.
 * @copyright 1997-2008 VerySimple, Inc. http://www.verysimple.com
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class FormValidator
{
    /**
     * Returns true if the provided email is valid
     *
     * @param string $email email address
     * @return bool
     */
    static function IsValidEmail(string $email): bool
    {
        return ValidationUtils::isValidEmail($email);
    }

    /**
     * Returns true if the provided credit card appears to be valid.
     * If type is provided, then the validation makes sure it is valid for that specific
     * type, otherwise it just makes sure it is valid for any type
     *
     * @param string $cc_num credit card number
     * @param string $type type [optional] (American, Dinners, Discover, Master, Visa)
     * @return bool
     */
    static function IsValidCreditCard(string $cc_num, string $type = ""): bool
    {
        return ValidationUtils::isValidCreditCard($cc_num, $type);
    }
}
