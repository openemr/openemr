<?php

/**
 * Builds an UPDATE for one layout column on the patient or the visit.
 *
 * The field id is checked against the live table, then quoted. It is not
 * copied into the SQL text. A column added to the table later still saves.
 * Identity columns are refused.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Database;

final class LayoutColumnUpdate
{
    /**
     * A layout field must not rewrite the row key.
     *
     * @var list<string>
     */
    private const IDENTITY = [
        'id',
        'uuid',
        'pid',
        'encounter',
    ];

    public static function patientStatement(string $fieldId): string
    {
        return self::statement(
            'patient_data',
            $fieldId,
            'UPDATE patient_data SET ',
            ' = ? WHERE pid = ?'
        );
    }

    public static function encounterStatement(string $fieldId): string
    {
        return self::statement(
            'form_encounter',
            $fieldId,
            'UPDATE form_encounter SET ',
            ' = ? WHERE pid = ? AND encounter = ?'
        );
    }

    private static function statement(string $table, string $fieldId, string $before, string $after): string
    {
        if ($fieldId === '' || in_array($fieldId, self::IDENTITY, true) || str_contains($fieldId, '`')) {
            throw new SqlQueryException('', 'The layout column cannot be saved.');
        }

        try {
            $quoted = escape_sql_column_name($fieldId, [$table], false, true);
        } catch (SqlQueryException $exception) {
            throw new SqlQueryException('', 'The layout column cannot be saved.', 0, $exception);
        }

        if (preg_match('/^`[A-Za-z0-9_]+`$/', $quoted) !== 1) {
            throw new SqlQueryException('', 'The layout column cannot be saved.');
        }

        return $before . $quoted . $after;
    }
}
