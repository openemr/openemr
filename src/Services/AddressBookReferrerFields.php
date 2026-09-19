<?php

/**
 * Referring-provider field checks for the address book.
 *
 * Person entries used on claims need a mailing address, and a valid NPI
 * when that requirement is enabled. NPI is the US HIPAA identifier from
 * CMS/NPPES, not a locale setting. Labs, vendors, and local login users
 * skip those rules.
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
use OpenEMR\Core\OEGlobalsBag;

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
     * Company types use list_options.option_value 3 (labs and vendors). That
     * column is FLOAT, so a driver may return '3' or 3.0. Compare the same
     * way addrbook_edit.php does ($option_abook_type == 3). asString() would
     * turn a numeric 3 into '' and treat labs as referring providers.
     *
     * @param mixed $username          users.username; empty for address-book-only rows
     * @param mixed $abookOptionValue  list_options.option_value for abook_type
     */
    public static function isExternalPerson(mixed $username, mixed $abookOptionValue): bool
    {
        return self::asString($username) === '' && $abookOptionValue != 3;
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
     * True when Address Book should require a valid NPI on external persons.
     *
     * NPI is US-only (CMS/NPPES). This is not tied to UI language. New
     * installs default on. Upgrades default off, then turn on when Units
     * for Visit Forms is already US-primary or US-only.
     */
    public static function npiRequired(): bool
    {
        return OEGlobalsBag::getInstance()->getBoolean('addrbook_require_npi', true);
    }

    /**
     * True when an external person save may proceed.
     *
     * @param mixed $npi
     * @param mixed $street
     * @param mixed $city
     * @param mixed $state
     * @param mixed $zip
     * @param bool  $requireNpi When false, only the mailing address is required.
     */
    public static function saveAllowed(
        mixed $npi,
        mixed $street,
        mixed $city,
        mixed $state,
        mixed $zip,
        bool $requireNpi = true
    ): bool {
        if ($requireNpi && !self::npiIsValid($npi)) {
            return false;
        }
        return self::mailingAddressComplete($street, $city, $state, $zip);
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

    /**
     * users columns that have a matching form_* field on the address-book editor.
     *
     * @return list<string>
     */
    public static function editorRowColumns(): array
    {
        return [
            'abook_type', 'title', 'fname', 'lname', 'mname', 'suffix',
            'specialty', 'organization', 'valedictory', 'assistant',
            'federaltaxid', 'upin', 'npi', 'taxonomy', 'cpoe',
            'email', 'email_direct', 'url', 'street', 'streetb',
            'city', 'state', 'country_code', 'zip', 'street2',
            'streetb2', 'city2', 'state2', 'country_code2', 'zip2',
            'phone', 'phonew1', 'phonew2', 'phonecell', 'fax', 'notes',
        ];
    }

    /**
     * Copy posted form_* values onto a users row after a rejected save.
     *
     * form_cpoe is a checkbox with no hidden fallback, so an absent key means
     * unchecked. Other missing keys keep the loaded row value.
     *
     * @param array<mixed> $row
     * @param callable(string): bool $has
     * @param callable(string): mixed $get
     * @return array<mixed>
     */
    public static function applyPostedEditorFields(array $row, callable $has, callable $get): array
    {
        foreach (self::editorRowColumns() as $col) {
            $postkey = 'form_' . $col;
            if ($has($postkey)) {
                $row[$col] = $get($postkey);
            } elseif ($col === 'cpoe') {
                $row['cpoe'] = '0';
            }
        }

        return $row;
    }
}
