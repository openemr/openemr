<?php

/**
 * PortalLoginRowNormalizer — pure functions that map raw rows produced by the legacy
 * ADODB-backed SQL helpers (where every column comes back as a string regardless of
 * its declared type) to the typed shapes declared on PortalLoginCredentialsRepository.
 *
 * Lives separately from SqlPortalLoginCredentialsRepository so the normalization
 * logic is unit-testable without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\Portal;

/**
 * @phpstan-import-type PatientAccessOnsiteRow from PortalLoginCredentialsRepository
 * @phpstan-import-type PatientDataRow from PortalLoginCredentialsRepository
 * @phpstan-import-type ProviderInfoRow from PortalLoginCredentialsRepository
 */
final class PortalLoginRowNormalizer
{
    /**
     * @param array<int|string, mixed> $row Raw row from QueryUtils::querySingleRow.
     * @return PatientAccessOnsiteRow
     */
    public static function authRow(array $row, bool $includeOneTime): array
    {
        $normalized = [
            'id' => self::intColumn($row['id'] ?? null),
            'pid' => self::intColumn($row['pid'] ?? null),
            'portal_pwd' => self::stringColumn($row['portal_pwd'] ?? null),
            'portal_username' => self::stringColumn($row['portal_username'] ?? null),
            'portal_login_username' => self::stringColumn($row['portal_login_username'] ?? null),
            'portal_pwd_status' => self::intColumn($row['portal_pwd_status'] ?? null),
        ];
        if ($includeOneTime) {
            $oneTime = $row['portal_onetime'] ?? null;
            $normalized['portal_onetime'] = $oneTime === null ? null : self::stringColumn($oneTime);
        }
        return $normalized;
    }

    /**
     * @param array<int|string, mixed> $row Raw row from QueryUtils::querySingleRow.
     * @return PatientDataRow
     */
    public static function patientDataRow(array $row): array
    {
        return [
            'pid' => self::intColumn($row['pid'] ?? null),
            'fname' => self::stringColumn($row['fname'] ?? null),
            'lname' => self::stringColumn($row['lname'] ?? null),
            'email' => self::stringColumn($row['email'] ?? null),
            'providerID' => self::intColumn($row['providerID'] ?? null),
            'allow_patient_portal' => self::stringColumn($row['allow_patient_portal'] ?? null),
        ];
    }

    /**
     * @param array<int|string, mixed> $row Result of getUserIDInfo() or equivalent.
     * @return ProviderInfoRow
     */
    public static function providerInfoRow(array $row): array
    {
        $username = $row['username'] ?? null;
        return [
            'fname' => self::stringColumn($row['fname'] ?? null),
            'lname' => self::stringColumn($row['lname'] ?? null),
            'username' => $username === null ? null : self::stringColumn($username),
        ];
    }

    /**
     * Coerce a DB-or-input value to int when it's a sane integer-like value; default
     * to 0 otherwise. ADODB returns numeric columns as strings — `is_numeric` covers
     * the typical case, and the null-default handles missing keys.
     */
    private static function intColumn(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }
        return 0;
    }

    /**
     * Coerce a DB-or-input value to string when it's a sane string-like value; default
     * to '' otherwise.
     */
    private static function stringColumn(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        return '';
    }
}
