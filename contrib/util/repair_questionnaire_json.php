<?php

/**
 * repair_questionnaire_json.php
 *
 * Repairs questionnaire_repository rows whose stored FHIR Questionnaire JSON
 * contains double-encoded array fields on items (e.g. "enableWhen" saved as a
 * JSON string instead of an array), which the strict generated FHIR model
 * classes reject at read time.
 *
 * Usage (from the openemr root):
 *   php contrib/util/repair_questionnaire_json.php [--site=default] [--id=50 --id=56] [--fix]
 *
 * Without --fix the script is a dry run: it reports what it would change and
 * touches nothing. With --fix it writes a backup of each original row to a
 * .json file next to this script before updating the database.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Contrib\Util;

use mysqli;
use mysqli_result;
use mysqli_stmt;

class QuestionnaireJsonRepair
{
    public const ITEM_ARRAY_FIELDS = ['enableWhen', 'code', 'answerOption', 'initial', 'extension', 'modifierExtension', 'item'];

    /**
     * Recursively repair double-encoded array fields on a questionnaire item tree.
     *
     * @param array<mixed> $node A Questionnaire or item node holding 'item'
     * @param list<string> $log Accumulates human-readable repair notes
     * @return array{0: array<mixed>, 1: bool} [repaired node, whether anything changed]
     */
    public static function repairNode(array $node, array &$log, string $path = 'Questionnaire'): array
    {
        $changed = false;
        if (!isset($node['item']) || !is_array($node['item']) || $node['item'] === []) {
            return [$node, false];
        }
        foreach ($node['item'] as $index => $item) {
            if (!is_array($item)) {
                $log[] = "$path.item[" . (string) $index . "]: item is " . gettype($item) . ", left untouched (read path will skip it)";
                continue;
            }
            $linkId = isset($item['linkId']) && is_scalar($item['linkId']) ? (string) $item['linkId'] : (string) $index;
            $itemPath = $path . '.item[' . $linkId . ']';
            foreach (self::ITEM_ARRAY_FIELDS as $field) {
                if ($field === 'item' || !isset($item[$field]) || is_array($item[$field])) {
                    continue;
                }
                if (is_string($item[$field])) {
                    $decoded = json_decode($item[$field], true);
                    if (is_array($decoded)) {
                        $item[$field] = $decoded;
                        $changed = true;
                        $log[] = "$itemPath.$field: decoded double-encoded string back to array";
                        continue;
                    }
                }
                $log[] = "$itemPath.$field: " . gettype($item[$field]) . " is unrepairable, left untouched (read path will drop it)";
            }
            // handle a double-encoded child item list, then recurse
            if (isset($item['item']) && is_string($item['item'])) {
                $decoded = json_decode($item['item'], true);
                if (is_array($decoded)) {
                    $item['item'] = $decoded;
                    $changed = true;
                    $log[] = "$itemPath.item: decoded double-encoded string back to array";
                }
            }
            [$item, $childChanged] = self::repairNode($item, $log, $itemPath);
            $changed = $changed || $childChanged;
            $node['item'][$index] = $item;
        }
        return [$node, $changed];
    }

    /**
     * Read a string value from the loaded sqlconf variables.
     *
     * @param array<mixed> $conf
     */
    private static function confString(array $conf, string $key, string $default = ''): string
    {
        $value = $conf[$key] ?? null;
        return is_string($value) ? $value : $default;
    }

    /**
     * @param list<string> $argv
     */
    public static function main(array $argv): int
    {
        $site = 'default';
        $fix = false;
        $ids = [];
        foreach (array_slice($argv, 1) as $arg) {
            if (preg_match('/^--site=(.+)$/', $arg, $m) === 1) {
                $site = $m[1];
            } elseif (preg_match('/^--id=(\d+)$/', $arg, $m) === 1) {
                $ids[] = (int) $m[1];
            } elseif ($arg === '--fix') {
                $fix = true;
            } else {
                fwrite(STDERR, "Unknown argument: $arg\n");
                return 1;
            }
        }

        $sqlconf = __DIR__ . '/../../sites/' . $site . '/sqlconf.php';
        if (!file_exists($sqlconf)) {
            fwrite(STDERR, "Cannot find $sqlconf - run from the openemr root or pass --site\n");
            return 1;
        }
        $conf = (static function (string $file): array {
            require $file;
            return get_defined_vars();
        })($sqlconf);
        $portValue = $conf['port'] ?? null;
        $port = is_numeric($portValue) && (int) $portValue > 0 ? (int) $portValue : 3306;
        $mysqli = mysqli_connect(
            self::confString($conf, 'host', 'localhost'),
            self::confString($conf, 'login'),
            self::confString($conf, 'pass'),
            self::confString($conf, 'dbase'),
            $port
        );
        if (!$mysqli instanceof mysqli) {
            fwrite(STDERR, "DB connection failed: " . (mysqli_connect_error() ?? 'unknown') . "\n");
            return 1;
        }
        mysqli_set_charset($mysqli, 'utf8mb4');

        $sql = "SELECT id, name, questionnaire FROM questionnaire_repository WHERE questionnaire IS NOT NULL AND questionnaire != ''";
        if ($ids !== []) {
            $sql .= " AND id IN (" . implode(',', $ids) . ")";
        }
        $result = mysqli_query($mysqli, $sql);
        if (!$result instanceof mysqli_result) {
            fwrite(STDERR, "Query failed: " . mysqli_error($mysqli) . "\n");
            return 1;
        }

        $mode = $fix ? 'FIX' : 'DRY RUN';
        echo "== repair_questionnaire_json ($mode, site=$site) ==\n";
        $repairedCount = 0;
        while (($row = mysqli_fetch_assoc($result)) !== null) {
            $rowId = isset($row['id']) && is_numeric($row['id']) ? (int) $row['id'] : 0;
            $rowName = is_string($row['name'] ?? null) ? $row['name'] : '';
            $rowJson = is_string($row['questionnaire'] ?? null) ? $row['questionnaire'] : '';
            $decoded = json_decode($rowJson, true);
            if (!is_array($decoded)) {
                echo "[id $rowId] $rowName: stored JSON does not decode - skipping (needs re-import)\n";
                continue;
            }
            $log = [];
            [$repaired, $changed] = self::repairNode($decoded, $log);
            if ($log === []) {
                continue;
            }
            echo "[id $rowId] $rowName:\n";
            foreach ($log as $line) {
                echo "    $line\n";
            }
            if (!$changed) {
                continue;
            }
            $repairedCount++;
            if (!$fix) {
                echo "    would update (dry run)\n";
                continue;
            }
            $backupFile = __DIR__ . '/questionnaire_' . $rowId . '_backup_' . date('Ymd_His') . '.json';
            if (file_put_contents($backupFile, $rowJson) === false) {
                fwrite(STDERR, "    backup to $backupFile failed - NOT updating this row\n");
                continue;
            }
            $newJson = json_encode($repaired, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $stmt = mysqli_prepare($mysqli, "UPDATE questionnaire_repository SET questionnaire = ?, modified_date = current_timestamp() WHERE id = ?");
            if (!$stmt instanceof mysqli_stmt) {
                fwrite(STDERR, "    prepare failed: " . mysqli_error($mysqli) . "\n");
                continue;
            }
            mysqli_stmt_bind_param($stmt, 'si', $newJson, $rowId);
            if (mysqli_stmt_execute($stmt)) {
                echo "    updated (backup: " . basename($backupFile) . ")\n";
            } else {
                fwrite(STDERR, "    update failed: " . mysqli_stmt_error($stmt) . "\n");
            }
            mysqli_stmt_close($stmt);
        }
        echo "== done: $repairedCount row(s) " . ($fix ? "repaired" : "need repair") . " ==\n";
        return 0;
    }
}

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    /** @var list<string> $argv */
    exit(QuestionnaireJsonRepair::main($argv));
}
