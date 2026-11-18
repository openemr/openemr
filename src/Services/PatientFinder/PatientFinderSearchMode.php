<?php

/**
 * Which fields the Patient Finder's main search box matches, picked from the
 * dropdown next to the box.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

/**
 * Backed because the value arrives from the browser as the `searchMode` request parameter.
 */
enum PatientFinderSearchMode: string
{
    /** Every displayed, searchable column. */
    case All = 'all';
    /** Last, first and middle name. */
    case Name = 'name';
    /** Date of birth, typed in the site's date format. */
    case Dob = 'dob';
    /** Home, work, mobile and contact phone numbers, by their digits. */
    case Phone = 'phone';
    /** Street, street line 2, city, state and ZIP / postal code. */
    case Address = 'address';
    /** The ZIP / postal code. */
    case Zip = 'zip';
    /** Email and Direct addresses. */
    case Email = 'email';
    /** The External ID (pubpid), the patient's chart number. */
    case ExternalId = 'pubpid';
    /** The last digits of the Social Security number. */
    case Ssn = 'ssn';

    /**
     * Read the request parameter; a missing or unknown value searches every column.
     */
    public static function fromRequestValue(string $value): self
    {
        return self::tryFrom($value) ?? self::All;
    }

    /**
     * Where the term must match within each field. Address, phone and email are searched
     * by any part of them, such as a street name after the house number. None of those
     * fields are indexed, so this costs no more than matching the start.
     */
    public function termMatch(): PatientFinderTermMatch
    {
        return match ($this) {
            self::All, self::Name, self::Zip, self::ExternalId => PatientFinderTermMatch::Start,
            self::Phone, self::Address, self::Email => PatientFinderTermMatch::Anywhere,
            self::Ssn => PatientFinderTermMatch::End,
            self::Dob => PatientFinderTermMatch::Date,
        };
    }

    /**
     * Whether the term and the fields are compared by their digits alone, so 555-444-3333,
     * (555) 444 3333 and 5554443333 are the same number.
     */
    public function comparesDigits(): bool
    {
        return match ($this) {
            self::Phone, self::Ssn => true,
            self::All, self::Name, self::Dob, self::Address, self::Zip, self::Email, self::ExternalId => false,
        };
    }

    /**
     * The fewest digits a number left after taking off a country code may have, so a short
     * local number cannot match a longer one from another area.
     */
    private const MIN_NATIONAL_DIGITS = 9;

    /**
     * For a phone search, the term without a country code of 1 to 3 digits, each at least
     * 9 digits long, so +1 555 444 3333 also finds 555-444-3333. A stored number must equal
     * one of them. None for the other modes.
     *
     * @return list<string>
     */
    public function withoutCountryCode(string $term): array
    {
        if ($this !== self::Phone) {
            return [];
        }
        $numbers = [];
        for ($codeLength = 1; $codeLength <= 3; $codeLength++) {
            $number = substr($term, $codeLength);
            if (strlen($number) >= self::MIN_NATIONAL_DIGITS) {
                $numbers[] = $number;
            }
        }
        return $numbers;
    }

    /**
     * The term as this mode compares it: only its digits for a phone or SSN search.
     */
    public function normalizeTerm(string $term): string
    {
        return $this->comparesDigits() ? (preg_replace('/\D+/', '', $term) ?? '') : $term;
    }

    /**
     * The columns the main search box matches in this mode, or null for the displayed columns.
     *
     * @return list<PatientFinderColumn>|null
     */
    public function globalSearchColumns(): ?array
    {
        return match ($this) {
            self::All => null,
            self::Name => [PatientFinderColumn::fromTable(PatientFinderQueryBuilder::NAME_KEY, 'Name')],
            self::Dob => [PatientFinderColumn::fromTable(PatientFinderQueryBuilder::DOB_KEY, 'DOB')],
            self::Phone => [
                self::digits('phone_home', 'Home Phone'),
                self::digits('phone_biz', 'Work Phone'),
                self::digits('phone_cell', 'Mobile Phone'),
                self::digits('phone_contact', 'Emergency Phone'),
            ],
            self::Address => [
                PatientFinderColumn::fromTable('street', 'Address'),
                PatientFinderColumn::fromTable('street_line_2', 'Address Line 2'),
                PatientFinderColumn::fromTable('city', 'City'),
                PatientFinderColumn::fromTable('state', 'State'),
                PatientFinderColumn::fromTable('postal_code', 'ZIP'),
            ],
            self::Zip => [PatientFinderColumn::fromTable('postal_code', 'ZIP')],
            self::Email => [
                PatientFinderColumn::fromTable('email', 'Email'),
                PatientFinderColumn::fromTable('email_direct', 'Direct Address'),
            ],
            self::ExternalId => [PatientFinderColumn::fromTable('pubpid', 'External ID')],
            self::Ssn => [self::digits('ss', 'SSN')],
        };
    }

    /**
     * A patient_data field searched by its digits: the separators people type in a phone
     * number or SSN are removed. REPLACE rather than REGEXP_REPLACE, which MySQL 5.7 lacks.
     * $field is one of this enum's own literals, never input.
     */
    private static function digits(string $field, string $title): PatientFinderColumn
    {
        $expression = "patient_data.$field";
        foreach (['-', ' ', '(', ')', '.', '+', '/'] as $separator) {
            $expression = "REPLACE($expression, '$separator', '')";
        }
        return PatientFinderColumn::fromSql($field . '_digits', $title, $expression);
    }
}
