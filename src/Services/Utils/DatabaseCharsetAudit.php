<?php

/**
 * Finds character columns that have not been upgraded to utf8mb4.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Utils;

use OpenEMR\Common\Database\QueryUtils;

final class DatabaseCharsetAudit
{
    /**
     * Return tables whose default charset or one or more character columns
     * are not utf8mb4, grouped by the legacy charset found.
     *
     * @return array<string, list<string>>
     */
    public function getTablesWithLegacyCharacterSets(): array
    {
        $rows = QueryUtils::fetchRecords(<<<'SQL'
            SELECT DISTINCT
                t.TABLE_NAME,
                t.TABLE_COLLATION,
                c.CHARACTER_SET_NAME
            FROM information_schema.TABLES AS t
            LEFT JOIN information_schema.COLUMNS AS c
                ON c.TABLE_SCHEMA = t.TABLE_SCHEMA
                AND c.TABLE_NAME = t.TABLE_NAME
            WHERE t.TABLE_SCHEMA = DATABASE()
                AND t.TABLE_TYPE = 'BASE TABLE'
                AND (
                    (t.TABLE_COLLATION IS NOT NULL AND LEFT(t.TABLE_COLLATION, 7) <> 'utf8mb4')
                    OR (c.CHARACTER_SET_NAME IS NOT NULL AND c.CHARACTER_SET_NAME <> 'utf8mb4')
                )
            ORDER BY t.TABLE_NAME, c.CHARACTER_SET_NAME
            SQL);

        return self::groupLegacyTables($rows);
    }

    /**
     * @param list<array<mixed>> $rows
     * @return array<string, list<string>>
     */
    public static function groupLegacyTables(array $rows): array
    {
        $tablesByCharacterSet = [];
        foreach ($rows as $row) {
            $tableName = $row['TABLE_NAME'] ?? null;
            $tableCollation = $row['TABLE_COLLATION'] ?? null;
            $columnCharacterSet = $row['CHARACTER_SET_NAME'] ?? null;
            if (!is_string($tableName)) {
                throw new \UnexpectedValueException('Database charset audit returned a row without a table name');
            }

            $characterSets = [];
            if (is_string($tableCollation)) {
                $tableCharacterSet = explode('_', $tableCollation, 2)[0];
                if ($tableCharacterSet !== 'utf8mb4') {
                    $characterSets[] = self::normalizeCharacterSet($tableCharacterSet);
                }
            }
            if (is_string($columnCharacterSet) && $columnCharacterSet !== 'utf8mb4') {
                $characterSets[] = self::normalizeCharacterSet($columnCharacterSet);
            }

            foreach (array_unique($characterSets) as $characterSet) {
                $tablesByCharacterSet[$characterSet][$tableName] = $tableName;
            }
        }

        $result = [];
        foreach ($tablesByCharacterSet as $characterSet => $tables) {
            $tableNames = array_values($tables);
            sort($tableNames);
            $result[$characterSet] = $tableNames;
        }
        ksort($result);

        return $result;
    }

    private static function normalizeCharacterSet(string $characterSet): string
    {
        return $characterSet === 'utf8' ? 'utf8mb3' : $characterSet;
    }
}
