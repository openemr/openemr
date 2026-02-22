<?php

/**
 * Related Persons Service
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Service for handling Related Persons fields on Demographics forms.
 * - Knows which columns exist in patient_related_persons
 * - Merges DB values into $result so the form pre-fills
 * - Collects form_* POST values, strips them from $_POST
 * - Upserts into patient_related_persons keyed by pid
 *
 * This class was created by ChatGPT and then modified.
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;

class DemographicsRelatedPersonsService
{
    /** @var string */
    private string $table = 'patient_related_persons';

    /** @var string[] Base column name stems for each related person */
    private array $fieldBases = [
        'uuid',
        'related_firstname_', 'related_lastname_', 'related_relationship_', 'related_sex_',
        'related_address_', 'related_city_', 'related_state_', 'related_postalcode_',
        'related_country_', 'related_phone_', 'related_workphone_', 'related_email_',
    ];

    /**
     * @param int $maxPeople Max number of related person blocks to fall back to if schema introspection isn't available
     */
    public function __construct(
        private readonly int $maxPeople = 3
    ) {
    }

    /**
     * Return all related-person column names (excluding pid).
     * Prefers INFORMATION_SCHEMA; falls back to a static set (1..$maxPeople).
     */
    public function getColumnNames(): array
    {
        $cols = [];
        // Try to read the actual table schema
        $res = sqlStatement(
            "SELECT COLUMN_NAME
               FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME LIKE 'related_%';",
            [$this->table]
        );
        while ($r = sqlFetchArray($res)) {
            $cols[] = $r['COLUMN_NAME'];
        }

        // Fallback if INFORMATION_SCHEMA isn't available or the table is empty
        if (!$cols) {
            for ($i = 1; $i <= $this->maxPeople; $i++) {
                foreach ($this->fieldBases as $b) {
                    $cols[] = "{$b}{$i}";
                }
            }
        }

        $col[] = 'uuid'; // Ensure uuid_1 is always included
        return $cols;
    }

    /**
     * Column => value map for a pid. If not found, returns all columns with nulls.
     */
    public function getMapForPid(int $pid): array
    {
        $cols = $this->getColumnNames();
        $map = array_fill_keys($cols, null);

        if ($pid <= 0) {
            return $map;
        }

        $colList = implode(',', array_map(static fn($c): string => "`$c`", $cols));
        $row = sqlQuery("SELECT $colList FROM `{$this->table}` WHERE `pid` = ?", [$pid]);

        if (is_array($row)) {
            foreach ($cols as $c) {
                $map[$c] = $row[$c] ?? null;
            }
        }

        return $map;
    }

    /**
     * Merge related-person values into $result so your form pre-fills.
     * - Adds any missing related* keys
     * - Preserves existing $result values
     * - Optionally overlays current POST (so user sees what they just typed after a validation error)
     */
    public function mergeIntoResult(int $pid, array &$result, bool $overlayPost = true): void
    {
        $map = $this->getMapForPid($pid);

        // Add missing keys without overwriting existing
        $result += $map;

        if ($overlayPost && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            foreach ($map as $col => $_) {
                $pkey = 'form_' . $col; // e.g., form_relatedfirstname_1
                if (array_key_exists($pkey, $_POST)) {
                    $val = $_POST[$pkey];
                    $result[$col] = is_string($val) ? trim($val) : $val;
                }
            }
        }
    }

    /**
     * Collect form_* POST values for related persons, normalize empty -> null,
     * and REMOVE them from $_POST so they don't flow into patient_data.
     *
     * Returns [ column => value, ... ].
     */
    public function collectFromPostAndStrip(array &$post): array
    {
        $cols = $this->getColumnNames();
        $out = [];

        foreach ($cols as $col) {
            $pkey = 'form_' . $col;
            if (array_key_exists($pkey, $post)) {
                $val = $post[$pkey];
                if (is_string($val)) {
                    $val = trim($val);
                    if ($val === '') {
                        $val = null;
                    }
                }
                $out[$col] = $val;
                unset($post[$pkey]); // prevent $newdata from picking it up
            }
        }

        return $out;
    }

    /**
     * Upsert values into patient_related_persons for a pid.
     */
    public function saveForPid(int $pid, array $values): void
    {
        if ($pid <= 0 || !$values) {
            return;
        }

        // Keep only known columns
        $allowed = array_flip($this->getColumnNames());
        $values = array_intersect_key($values, $allowed);
        if (!$values) {
            return;
        }

        $cols = array_keys($values);
        $insertCols = array_merge(['pid'], $cols);
        $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
        $assignments = implode(', ', array_map(static fn($c): string => "`$c` = ?", $cols));
        $params = array_merge([$pid], array_values($values), array_values($values));

        $sql = "INSERT INTO `{$this->table}` (`" . implode('`,`', $insertCols) . "`)
                VALUES ($placeholders)
                ON DUPLICATE KEY UPDATE $assignments";
        sqlStatement($sql, $params);

        UuidRegistry::createMissingUuidsForTables([$this->table]);
    }

    /**
     * Helper to use inside the DEM layout loop if you prefer to early-skip these fields.
     */
    public function isRelatedFieldId(string $fieldId): bool
    {
        return (bool)preg_match(
            '/^related_(?:firstname|lastname|relationship|sex|address|city|state|postalcode|country|phone|workphone|email)_(?:\d+)$/',
            $fieldId
        );
    }
}
