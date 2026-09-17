<?php

/**
 * Referring-provider field checks for the address book.
 *
 * Person entries used on claims need a 10-digit NPI and a mailing address.
 * Labs, vendors, and local login users skip those rules.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

final class AddressBookReferrerFields
{
    public static function asString(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    public static function isExternalPerson(mixed $username, mixed $abookOptionValue): bool
    {
        return self::asString($username) === '' && self::asString($abookOptionValue) !== '3';
    }

    public static function npiIsTenDigits(mixed $npi): bool
    {
        return (bool) preg_match('/^\d{10}$/', self::asString($npi));
    }

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

    public static function saveAllowed(
        mixed $npi,
        mixed $street,
        mixed $city,
        mixed $state,
        mixed $zip
    ): bool {
        return self::npiIsTenDigits($npi) && self::mailingAddressComplete($street, $city, $state, $zip);
    }

    public static function npiMissingOnList(mixed $npi): bool
    {
        return self::asString($npi) === '';
    }
}
