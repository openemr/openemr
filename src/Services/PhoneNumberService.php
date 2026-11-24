<?php

/**
 * PhoneService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2023 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\ValueObjects\PhoneNumber;
use OpenEMR\Common\ValueObjects\TypedPhoneNumber;
use Particle\Validator\Validator;

class PhoneNumberService extends BaseService
{
    public const COUNTRY_CODE = "+1";
    public string $area_code;
    public string $prefix;
    public string $number;
    public int    $type;
    private int   $foreignId;

    public function __construct()
    {
        $this->area_code = $area_code ?? '';
        $this->prefix = $prefix ?? '';
        $this->number = $number ?? '';
        $this->type = $type ?? 2;
        $this->foreignId = $foreignId ?? 0;
    }

    public function validate($phoneNumber)
    {
        $validator = new Validator();

        $validator->optional('country_code')->lengthBetween(1, 5);
        $validator->optional('area_code')->lengthBetween(1, 3);
        $validator->optional('prefix')->lengthBetween(1, 3);
        $validator->optional('number')->lengthBetween(1, 4);
        $validator->optional('type')->lengthBetween(1, 11);
        $validator->optional('foreign_id')->lengthBetween(1, 11);

        return $validator->validate($phoneNumber);
    }

    /**
     * @param ?PhoneType $type the number's type; null stores it as $this->type (work, unless set)
     */
    public function insert($data, $foreignId, ?PhoneType $type = null): int
    {
        $freshId = $this->getFreshId("id", "phone_numbers");
        $this->foreignId = $foreignId;

        $this->getPhoneParts($data['phone']);

        $phoneNumbersSql  = " INSERT INTO phone_numbers SET";
        $phoneNumbersSql .= "     id=?,";
        $phoneNumbersSql .= "     country_code=?,";
        $phoneNumbersSql .= "     area_code=?,";
        $phoneNumbersSql .= "     prefix=?,";
        $phoneNumbersSql .= "     number=?,";
        $phoneNumbersSql .= "     type=?,";
        $phoneNumbersSql .= "     foreign_id=?";

        QueryUtils::sqlInsert(
            $phoneNumbersSql,
            [
                $freshId,
                self::COUNTRY_CODE,
                $this->area_code,
                $this->prefix,
                $this->number,
                $type->value ?? $this->type,
                $this->foreignId
            ]
        );

        return $freshId;
    }

    public function update($data, $foreignId)
    {
        $this->foreignId = $foreignId;
        $this->getPhoneParts($data['phone']);

        $phoneNumbersSql  = " UPDATE phone_numbers SET";
        $phoneNumbersSql .= "     country_code=?,";
        $phoneNumbersSql .= "     area_code=?,";
        $phoneNumbersSql .= "     prefix=?,";
        $phoneNumbersSql .= "     number=? ";
        $phoneNumbersSql .= "     WHERE foreign_id=? AND type=?";

        $phoneNumbersSqlResults = sqlStatement(
            $phoneNumbersSql,
            [
                self::COUNTRY_CODE,
                $this->area_code ,
                $this->prefix,
                $this->number,
                $this->foreignId,
                $this->type
            ]
        );

        if (!$phoneNumbersSqlResults) {
            return false;
        }

        $phoneNumbersIdSqlResults = sqlQuery("SELECT id FROM phone_numbers WHERE foreign_id=?", [$this->foreignId]);

        if (!$phoneNumbersIdSqlResults) {
            $this->insert($data, $foreignId);
        }
        return $phoneNumbersIdSqlResults["id"] ?? null;
    }

    public function getPhoneParts(string $phone_number)
    {
        $phone_parts = [];
        preg_match(
            "/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",
            $phone_number,
            $phone_parts
        );

        $this->area_code = $phone_parts[1] ?? '';
        $this->prefix = $phone_parts[2] ?? '';
        $this->number = $phone_parts[3] ?? '';
    }

    public function getOneByForeignId($foreignId)
    {
        $sql = "SELECT * FROM phone_numbers WHERE foreign_id=?";
        return sqlQuery($sql, [$foreignId]);
    }

    /**
     * Get all phone numbers for a given foreign ID.
     *
     * @param int $foreignId The foreign ID to search for
     * @return list<array<mixed>> Array of phone number records ordered by type, then id
     */
    public function getPhonesByForeignId(int $foreignId): array
    {
        $sql = 'SELECT * FROM phone_numbers WHERE foreign_id = ? ORDER BY type, id';
        return QueryUtils::fetchRecords($sql, [$foreignId]);
    }

    /**
     * The newest saved WORK and FAX number of a record, at most one of each.
     *
     * Older saves could leave several rows of a type; only the newest counts.
     * A type whose newest row has no 10-digit national number is left out
     * rather than answered with an older row, so replaceNumbers() keeps that
     * type's saved rows instead of overwriting the newest with an older one.
     *
     * @return list<TypedPhoneNumber>
     */
    public function getWorkAndFaxNumbers(int $foreignId): array
    {
        $newest = [];
        foreach ($this->getPhonesByForeignId($foreignId) as $record) {
            $type = is_int($record['type']) ? PhoneType::tryFrom($record['type']) : null;
            if ($type !== PhoneType::WORK && $type !== PhoneType::FAX) {
                continue;
            }
            $parts = '';
            foreach (['area_code', 'prefix', 'number'] as $column) {
                $parts .= is_string($record[$column] ?? null) ? $record[$column] : '';
            }
            // Rows arrive in id order within a type, so the last one wins.
            $newest[$type->value] = [$type, $parts];
        }
        $numbers = [];
        foreach ($newest as [$type, $parts]) {
            $typed = TypedPhoneNumber::tryCreate($parts, $type);
            if ($typed?->phoneNumber->getNationalDigits() !== null) {
                $numbers[] = $typed;
            }
        }
        return $numbers;
    }

    /**
     * Replace a record's saved rows of each type in $numbers with that number.
     *
     * Each type's rows are cleared before its number is inserted, which
     * collapses duplicate rows that older saves left. A type with no entry
     * in $numbers keeps its rows, as does an entry with no 10-digit national
     * number, which the area_code/prefix/number columns cannot store.
     *
     * @param list<TypedPhoneNumber> $numbers at most one per type
     */
    public function replaceNumbers(int $foreignId, array $numbers): void
    {
        foreach ($numbers as $phone) {
            $nationalDigits = $phone->phoneNumber->getNationalDigits();
            if ($nationalDigits === null) {
                continue;
            }
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM phone_numbers WHERE foreign_id = ? AND type = ?',
                [$foreignId, $phone->type->value]
            );
            $this->type = $phone->type->value;
            $this->insert(['phone' => $nationalDigits], $foreignId);
        }
    }

    /**
     * Format phone number parts for display (legacy array format).
     *
     * @param array $phoneData Array with 'area_code', 'prefix', and 'number' keys
     * @return string Formatted phone number (XXX-XXX-XXXX) or empty string
     * @deprecated Use PhoneNumber::formatLocal() instead for locale-aware formatting
     */
    public static function getPhoneDisplay(array $phoneData): string
    {
        $areaCode = $phoneData['area_code'] ?? '';
        $prefix = $phoneData['prefix'] ?? '';
        $number = $phoneData['number'] ?? '';

        if (is_numeric($areaCode) && is_numeric($prefix) && is_numeric($number)) {
            return $areaCode . '-' . $prefix . '-' . $number;
        }

        return '';
    }

    /**
     * Parse a phone number string into its component parts.
     *
     * @param string $phone The phone number to parse
     * @param string $defaultRegion Default region code (default: 'US')
     * @param bool $strict If true, validates against real area codes
     * @return array{area_code: string, prefix: string, number: string}
     * @deprecated Use PhoneNumber::tryParse()->toParts() instead
     */
    public static function parsePhone(string $phone, string $defaultRegion = 'US', bool $strict = true): array
    {
        $parsed = PhoneNumber::tryParse($phone, $defaultRegion);
        if ($parsed === null) {
            return ['area_code' => '', 'prefix' => '', 'number' => ''];
        }
        $isValid = $strict ? $parsed->isValid() : $parsed->isPossible();
        if (!$isValid) {
            return ['area_code' => '', 'prefix' => '', 'number' => ''];
        }
        return $parsed->toParts();
    }

    /**
     * Format a phone number string for display.
     *
     * @param string $phone The phone number to format
     * @param string $defaultRegion Default region code (default: 'US')
     * @param bool $strict If true, validates against real area codes
     * @return string Locale-aware formatted phone number or empty string
     * @deprecated Use PhoneNumber::tryParse()->formatLocal() instead
     */
    public static function formatPhone(string $phone, string $defaultRegion = 'US', bool $strict = false): string
    {
        $parsed = PhoneNumber::tryParse($phone, $defaultRegion);
        if ($parsed === null) {
            return '';
        }
        $isValid = $strict ? $parsed->isValid() : $parsed->isPossible();
        if (!$isValid) {
            return '';
        }
        return $parsed->formatLocal();
    }

    /**
     * Try to format a phone number, returning original with warning on failure.
     *
     * Use this when you want to display a formatted phone number but need to
     * preserve the original input if formatting fails.
     *
     * @param string $phone The phone number to format
     * @param string $defaultRegion Default region code (default: 'US')
     * @return string Formatted phone number or original if formatting fails
     */
    public static function tryFormatPhone(string $phone, string $defaultRegion = 'US'): string
    {
        if ($phone === '') {
            return '';
        }
        $formatted = self::formatPhone($phone, $defaultRegion);
        if ($formatted === '') {
            ServiceContainer::getLogger()->warning("Could not format phone number", ['phone' => $phone]);
            return $phone;
        }
        return $formatted;
    }

    /**
     * Format a phone number for HL7 messaging.
     *
     * @param string $phone The phone number to format
     * @param string $defaultRegion Default region code (default: 'US')
     * @return string HL7 formatted phone number (XXX^XXXXXXX)
     * @deprecated Use PhoneNumber::tryParse()->toHL7() instead
     */
    public static function toHL7Phone(string $phone, string $defaultRegion = 'US'): string
    {
        $parsed = PhoneNumber::tryParse($phone, $defaultRegion);
        if ($parsed?->isPossible()) {
            return $parsed->toHL7();
        }
        // Fallback for 7-digit numbers
        $stripped = (string) preg_replace('/\D/', '', $phone);
        if (strlen($stripped) === 7) {
            return '000^' . $stripped;
        }
        return '000^0000000';
    }

    /**
     * Formats a phone number to E.164 format.
     *
     * @param string $phone The phone number to format
     * @param string $defaultRegion Default region code (default: 'US')
     * @param bool $strict If true, validates against real area codes
     * @return string|null E.164 formatted number or null if invalid
     * @deprecated Use PhoneNumber::tryParse()->toE164() instead
     */
    public static function toE164(string $phone, string $defaultRegion = 'US', bool $strict = true): ?string
    {
        $parsed = PhoneNumber::tryParse($phone, $defaultRegion);
        if ($parsed === null) {
            return null;
        }
        $isValid = $strict ? $parsed->isValid() : $parsed->isPossible();
        if (!$isValid) {
            return null;
        }
        return $parsed->toE164();
    }

    /**
     * Extracts the 10-digit national number from a phone number.
     *
     * @param string $phone The phone number to extract digits from
     * @param string $defaultRegion Default region code (default: 'US')
     * @param bool $strict If true, validates against real area codes
     * @return string|null 10-digit national number or null if invalid
     * @deprecated Use PhoneNumber::tryParse()->getNationalDigits() instead
     */
    public static function toNationalDigits(string $phone, string $defaultRegion = 'US', bool $strict = false): ?string
    {
        $parsed = PhoneNumber::tryParse($phone, $defaultRegion);
        if ($parsed === null) {
            return null;
        }
        $isValid = $strict ? $parsed->isValid() : $parsed->isPossible();
        if (!$isValid) {
            return null;
        }
        return $parsed->getNationalDigits();
    }
}
