<?php

/**
 * Applies HIS layout default_value settings when a history row is first created.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Layouts;

final class HistoryLayoutDefaults
{
    /**
     * Fill missing history fields from HIS layout defaults. Existing values, including empty strings, win.
     *
     * @param array<mixed> $record
     * @param list<array<mixed>> $layoutRows
     * @param array<string> $allowedColumns
     * @return array<mixed>
     */
    public static function apply(array $record, array $layoutRows, array $allowedColumns): array
    {
        $allowed = [];
        foreach ($allowedColumns as $column) {
            if ($column !== '') {
                $allowed[$column] = true;
            }
        }
        foreach (['id', 'uuid', 'pid', 'date', 'created_by'] as $skip) {
            unset($allowed[$skip]);
        }

        foreach ($layoutRows as $row) {
            $fieldId = $row['field_id'] ?? null;
            $default = $row['default_value'] ?? null;
            if (!is_string($fieldId) || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $fieldId) !== 1) {
                continue;
            }
            if (!isset($allowed[$fieldId]) || !is_string($default) || $default === '') {
                continue;
            }
            if (!array_key_exists($fieldId, $record)) {
                $record[$fieldId] = $default;
            }
        }

        return $record;
    }
}
