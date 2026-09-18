<?php

/**
 * Referring-provider field checks for the address book.
 *
 * Person entries used on claims need a valid 10-digit NPI and a mailing
 * address. Labs, vendors, and local login users skip those rules.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Utils\ValidationUtils;

final class AddressBookReferrerFields
{
    /**
     * Trim a posted or row value. Non-strings become an empty string.
     *
     * @param mixed $value Posted field or database column
     */
    public static function asString(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    /**
     * True when the row is an external person (not a local login, not a company).
     *
     * Company types use list_options.option_value 3 (labs and vendors).
     *
     * @param mixed $username          users.username; empty for address-book-only rows
     * @param mixed $abookOptionValue  list_options.option_value for abook_type
     */
    public static function isExternalPerson(mixed $username, mixed $abookOptionValue): bool
    {
        return self::asString($username) === '' && self::asString($abookOptionValue) !== '3';
    }

    /**
     * True when the value is a valid NPI (10 digits and Luhn with prefix 80840).
     *
     * @param mixed $npi Posted or stored NPI
     * @see ValidationUtils::isValidNPI()
     */
    public static function npiIsValid(mixed $npi): bool
    {
        return ValidationUtils::isValidNPI(self::asString($npi));
    }

    /**
     * True when street, city, state, and postal code are all non-empty.
     *
     * @param mixed $street
     * @param mixed $city
     * @param mixed $state
     * @param mixed $zip
     */
    public static function mailingAddressComplete(
        mixed $street,
        mixed $city,
        mixed $state,
        mixed $zip
    ): bool {
        return self::asString($street) !== ''
            && self::asString($city) !== ''
            && self::asString($state) !== ''
            && self::asString($zip) !== '';
    }

    /**
     * True when an external person save may proceed.
     *
     * @param mixed $npi
     * @param mixed $street
     * @param mixed $city
     * @param mixed $state
     * @param mixed $zip
     */
    public static function saveAllowed(
        mixed $npi,
        mixed $street,
        mixed $city,
        mixed $state,
        mixed $zip
    ): bool {
        return self::npiIsValid($npi) && self::mailingAddressComplete($street, $city, $state, $zip);
    }

    /**
     * True when the list should flag a missing NPI (empty, not merely invalid).
     *
     * @param mixed $npi
     */
    public static function npiMissingOnList(mixed $npi): bool
    {
        return self::asString($npi) === '';
    }
}
