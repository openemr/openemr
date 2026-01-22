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

    /**
     * Validates a National Provider Identifier (NPI).
     *
     * NPIs are 10-digit numbers that must pass the Luhn algorithm check
     * with the healthcare prefix 80840.
     *
     * @param string $npi The NPI to validate
     * @return bool True if valid NPI, false otherwise
     * @see https://www.cms.gov/Regulations-and-Guidance/Administrative-Simplification/NationalProvIdentStand
     */
    public static function isValidNPI(string $npi): bool
    {
        // NPI must be exactly 10 digits
        if (!preg_match('/^\d{10}$/', $npi)) {
            return false;
        }

        // Apply Luhn algorithm with 80840 prefix (ISO standard for US healthcare)
        // Prepend 80840 to make a 15-digit number for the check
        $prefixedNpi = '80840' . $npi;

        return self::luhnCheck($prefixedNpi);
    }

    /**
     * Performs Luhn algorithm check on a numeric string.
     *
     * @param string $number The number to check
     * @return bool True if the number passes the Luhn check
     */
    private static function luhnCheck(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$i];

            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return ($sum % 10) === 0;
    }

    /**
     * Validates a postal code based on country.
     *
     * @param string $postalCode The postal code to validate
     * @param string $country The country code (US, CA, etc.)
     * @return bool True if valid postal code for the country, false otherwise
     */
    public static function isValidPostalCode(string $postalCode, string $country = 'US'): bool
    {
        return match (strtoupper($country)) {
            'US' => self::isValidUSPostalCode($postalCode),
            'CA' => self::isValidCAPostalCode($postalCode),
            default => !empty($postalCode), // For other countries, just check non-empty
        };
    }

    /**
     * Validates a US postal code (ZIP code).
     *
     * Accepts 5-digit ZIP (12345) or ZIP+4 format (12345-6789).
     *
     * @param string $postalCode The postal code to validate
     * @return bool True if valid US postal code
     */
    public static function isValidUSPostalCode(string $postalCode): bool
    {
        return (bool) preg_match('/^\d{5}(-\d{4})?$/', $postalCode);
    }

    /**
     * Validates a Canadian postal code.
     *
     * Format: A1A 1A1 or A1A1A1 (letter-digit-letter space digit-letter-digit)
     *
     * @param string $postalCode The postal code to validate
     * @return bool True if valid Canadian postal code
     */
    public static function isValidCAPostalCode(string $postalCode): bool
    {
        return (bool) preg_match('/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/i', $postalCode);
    }

    /**
     * Validates a URL using filter_var.
     *
     * @param string $url The URL to validate
     * @param bool $requireHttps If true, only accept HTTPS URLs
     * @return bool True if valid URL, false otherwise
     */
    public static function isValidUrl(string $url, bool $requireHttps = false): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        if ($requireHttps) {
            return strtolower(parse_url($url, PHP_URL_SCHEME) ?? '') === 'https';
        }

        return true;
    }
}
