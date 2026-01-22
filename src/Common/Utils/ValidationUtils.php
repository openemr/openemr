<?php

/**
 * ValidationUtils is intended for validation methods that are used in OpenEMR.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
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
     * Validates an IP address using filter_var.
     *
     * @param string $ip The IP address to validate
     * @param int $flags Optional flags: FILTER_FLAG_IPV4, FILTER_FLAG_IPV6,
     *                   FILTER_FLAG_NO_PRIV_RANGE, FILTER_FLAG_NO_RES_RANGE
     * @return bool True if valid IP address, false otherwise
     */
    public static function isValidIpAddress(string $ip, int $flags = 0): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
    }

    /**
     * Validates an integer, optionally within a range.
     *
     * @param mixed $value The value to validate
     * @param ?int $min Minimum allowed value (inclusive)
     * @param ?int $max Maximum allowed value (inclusive)
     * @return int|false The validated integer, or false if invalid
     */
    public static function validateInt(mixed $value, ?int $min = null, ?int $max = null): int|false
    {
        $options = [];
        if ($min !== null) {
            $options['min_range'] = $min;
        }
        if ($max !== null) {
            $options['max_range'] = $max;
        }

        return filter_var($value, FILTER_VALIDATE_INT, empty($options) ? 0 : ['options' => $options]);
    }

    /**
     * Validates a float, optionally within a range.
     *
     * @param mixed $value The value to validate
     * @param ?float $min Minimum allowed value (inclusive)
     * @param ?float $max Maximum allowed value (inclusive)
     * @return float|false The validated float, or false if invalid
     */
    public static function validateFloat(mixed $value, ?float $min = null, ?float $max = null): float|false
    {
        $options = [];
        if ($min !== null) {
            $options['min_range'] = $min;
        }
        if ($max !== null) {
            $options['max_range'] = $max;
        }

        return filter_var($value, FILTER_VALIDATE_FLOAT, empty($options) ? 0 : ['options' => $options]);
    }
}
