<?php

/**
 * The family history the eye exam keeps in history_data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

/**
 * The eye exam's family history panel edits a fixed set of history_data
 * columns: the general medical "relatives" columns and the eye conditions it
 * keeps in usertext11-18. It doesn't depend on the History layout, which a
 * practice may hide or rearrange.
 */
final class FamilyHistory
{
    /**
     * The medical columns the panel lists, in the History layout's stock order.
     */
    public const MEDICAL = [
        'relatives_cancer',
        'relatives_tuberculosis',
        'relatives_diabetes',
        'relatives_high_blood_pressure',
        'relatives_heart_problems',
        'relatives_stroke',
        'relatives_epilepsy',
        'relatives_mental_illness',
        'relatives_suicide',
    ];

    /**
     * The eye condition columns (glaucoma, cataract, AMD, RD, ...).
     */
    public const EYE = [
        'usertext11',
        'usertext12',
        'usertext13',
        'usertext14',
        'usertext15',
        'usertext16',
        'usertext17',
        'usertext18',
    ];

    /**
     * The medical entries recorded in a history_data row, by column, in
     * MEDICAL order. A blank value, or one stored as "0" (none), is not
     * recorded.
     *
     * @param array<mixed> $history a history_data row
     * @return array<string, string>
     */
    public static function recordedMedical(array $history): array
    {
        $recorded = [];
        foreach (self::MEDICAL as $column) {
            $value = $history[$column] ?? null;
            if (!is_string($value) || $value === '' || preg_match('/^\|?0\|?\|?/', $value) === 1) {
                continue;
            }
            $recorded[$column] = $value;
        }
        return $recorded;
    }

    /**
     * The start of a recorded value the panels show, unescaped (the panels
     * escape it). Cut by character so text() doesn't blank a value cut
     * inside a multibyte character.
     */
    public static function display(string $value): string
    {
        return mb_substr($value, 0, 100);
    }

    /**
     * The family history columns a panel save should write: those the
     * request posted, with their values. Columns the panel doesn't post are
     * left out, so a save doesn't clear them. A posted value that isn't a
     * string is null, which updateValues() treats as not posted.
     *
     * @param array<mixed> $posted the request parameters
     * @return array<string, ?string>
     */
    public static function postedColumns(array $posted): array
    {
        $columns = [];
        foreach ([...self::MEDICAL, ...self::EYE] as $column) {
            if (!array_key_exists($column, $posted)) {
                continue;
            }
            $value = $posted[$column];
            $columns[$column] = is_string($value) ? $value : null;
        }
        return $columns;
    }

    /**
     * The panel save's UPDATE. It names every family history column and
     * keeps a column's stored value when its placeholder is null, so the SQL
     * never depends on what was posted.
     */
    public static function updateSql(): string
    {
        $assignments = array_map(
            static fn(string $column): string => "`$column` = COALESCE(?, `$column`)",
            [...self::MEDICAL, ...self::EYE]
        );
        return 'UPDATE history_data SET ' . implode(', ', $assignments) . ' WHERE pid = ?';
    }

    /**
     * The values for updateSql()'s column placeholders, in its order: the
     * posted value, or null to keep the stored one. The pid follows them.
     *
     * @param array<string, ?string> $columns from postedColumns()
     * @return list<?string>
     */
    public static function updateValues(array $columns): array
    {
        return array_map(
            static fn(string $column): ?string => $columns[$column] ?? null,
            [...self::MEDICAL, ...self::EYE]
        );
    }
}
