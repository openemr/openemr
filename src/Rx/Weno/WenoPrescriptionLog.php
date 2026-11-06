<?php

/**
 * Saves prescriptions from the WENO prescription log and maps its columns.
 *
 * Writes use the throwing QueryUtils API, so a failed row is reported to the
 * import instead of ending the request in HelpfulDie().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    James DuRant <jdurant@ad.unc.edu>
 * @copyright Copyright (c) 2026 James DuRant <jdurant@ad.unc.edu>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Weno;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;

final class WenoPrescriptionLog
{
    private const COLUMNS = [
        'active', 'date_added', 'patient_id', 'user_id', 'drug', 'quantity', 'refills',
        'substitute', 'note', 'rxnorm_drugcode', 'provider_id',
    ];

    /**
     * The prescriptions.substitute code for the log's substitution column:
     * 1 substitution allowed, 2 not allowed, 0 not stated.
     */
    public static function substituteValue(mixed $value): int
    {
        if (!is_scalar($value)) {
            return 0;
        }
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return 0;
        }
        return $value === 'allowed' ? 1 : 2;
    }

    /**
     * @param array<string, mixed> $row
     * @return true|string true when the row was written, otherwise the database error
     */
    public static function insert(array $row): true|string
    {
        try {
            QueryUtils::sqlInsert(
                'INSERT INTO prescriptions SET active = ?, date_added = ?, datetime = NOW(), patient_id = ?,'
                . ' provider_id = ?, drug = ?, quantity = ?, refills = ?, substitute = ?, note = ?,'
                . ' rxnorm_drugcode = ?, external_id = ?, indication = ?',
                [...self::values($row), $row['prescriptionguid'] ?? null]
            );
        } catch (SqlQueryException $e) {
            return $e->getMessage();
        }
        return true;
    }

    /**
     * Updates the prescription saved for this WENO message (matched by indication).
     *
     * @param array<string, mixed> $row
     * @return true|string true when the statement ran, otherwise the database error
     */
    public static function update(array $row): true|string
    {
        try {
            QueryUtils::sqlStatementThrowException(
                'UPDATE prescriptions SET active = ?, date_added = ?, datetime = NOW(), patient_id = ?,'
                . ' provider_id = ?, drug = ?, quantity = ?, refills = ?, substitute = ?, note = ?,'
                . ' rxnorm_drugcode = ?, external_id = ? WHERE indication = ?',
                [...self::values($row), $row['prescriptionguid'] ?? null]
            );
        } catch (SqlQueryException $e) {
            return $e->getMessage();
        }
        return true;
    }

    /**
     * @param array<string, mixed> $row
     * @return list<mixed>
     */
    private static function values(array $row): array
    {
        return array_map(static fn(string $column): mixed => $row[$column] ?? null, self::COLUMNS);
    }
}
