#!/usr/bin/env php
<?php

/**
 * Refresh RESERVED_WORD_SUPPLEMENT in ReservedWordRegistry from live
 * MySQL + MariaDB servers.
 *
 * Asks each engine which keywords it reserves, unions the answers,
 * diffs against the set phpmyadmin/sql-parser ships, and rewrites the
 * supplement constant in the registry. Snapshots both engines'
 * keyword tables to reserved-words/ so a diff in those files is the
 * audit record for what changed and when.
 *
 * Designed to be invoked from
 * .github/workflows/refresh-reserved-word-supplement.yml on a monthly
 * cron. The workflow opens a PR if anything in the repo changes.
 *
 * Connection details come from environment variables; defaults match
 * the workflow's service-container ports.
 *
 *   MYSQL_HOST     (default: 127.0.0.1)
 *   MYSQL_PORT     (default: 3306)
 *   MARIADB_HOST   (default: 127.0.0.1)
 *   MARIADB_PORT   (default: 3307)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpMyAdmin\SqlParser\Context;
use PhpMyAdmin\SqlParser\Token;

const SNAPSHOT_DIR = __DIR__ . '/../reserved-words';
const REGISTRY_FILE = __DIR__ . '/../tests/PHPStan/Rules/Sql/ReservedWordRegistry.php';

main();

function main(): void
{
    if (!is_dir(SNAPSHOT_DIR)) {
        @mkdir(SNAPSHOT_DIR, 0755, true);
    }

    $mysql = connect(
        getenv('MYSQL_HOST') ?: '127.0.0.1',
        (int) (getenv('MYSQL_PORT') ?: '3306'),
        'MySQL',
    );
    [$mysqlVersion, $mysqlKeywords] = dumpMysqlKeywords($mysql);
    writeSnapshot(SNAPSHOT_DIR . '/mysql.tsv', 'MySQL', $mysqlVersion, $mysqlKeywords);

    $mariadb = connect(
        getenv('MARIADB_HOST') ?: '127.0.0.1',
        (int) (getenv('MARIADB_PORT') ?: '3307'),
        'MariaDB',
    );
    [$mariadbVersion, $mariadbKeywords] = dumpMariadbKeywords($mariadb);
    writeSnapshot(SNAPSHOT_DIR . '/mariadb.tsv', 'MariaDB', $mariadbVersion, $mariadbKeywords);

    $authoritative = collectReserved($mysqlKeywords) + collectReserved($mariadbKeywords);
    fprintf(STDERR, "Authoritative reserved-word union: %d words.\n", count($authoritative));

    $library = collectLibraryReserved('MySql') + collectLibraryReserved('MariaDb');
    fprintf(STDERR, "phpmyadmin/sql-parser ships: %d reserved words.\n", count($library));

    // Single-word entries that the library does not cover. Composed
    // tokens (e.g. "ORDER BY") would never be bare column names so we
    // drop them.
    $missing = array_diff_key($authoritative, $library);
    $missing = array_filter(
        $missing,
        static fn(string $word): bool => preg_match('/^[a-z_][a-z0-9_]*$/', $word) === 1,
        ARRAY_FILTER_USE_KEY,
    );

    $supplement = array_keys($missing);
    sort($supplement);
    fprintf(STDERR, "Computed supplement: %d entries.\n", count($supplement));

    $current = readCurrentSupplement(REGISTRY_FILE);
    if ($current === $supplement) {
        fprintf(STDERR, "No drift -- supplement is current.\n");
        return;
    }

    fprintf(STDERR, "Drift detected. Rewriting %s.\n", REGISTRY_FILE);
    writeSupplement(REGISTRY_FILE, $supplement);

    fprintf(STDERR, "Done. Run `git diff` to review.\n");
}

function connect(string $host, int $port, string $label): mysqli
{
    $mysqli = @new mysqli($host, 'root', '', '', $port);
    if ($mysqli->connect_errno !== 0) {
        fail(sprintf('%s connect failed (%s:%d): %s', $label, $host, $port, $mysqli->connect_error ?? '?'));
    }
    return $mysqli;
}

/**
 * @return array{string, array<string, bool>}  [version, [WORD => reserved]]
 */
function dumpMysqlKeywords(mysqli $mysqli): array
{
    $version = scalarQuery($mysqli, 'SELECT VERSION()');

    $rows = $mysqli->query('SELECT WORD, RESERVED FROM INFORMATION_SCHEMA.KEYWORDS ORDER BY WORD');
    if (!$rows instanceof mysqli_result) {
        fail('MySQL INFORMATION_SCHEMA.KEYWORDS query failed: ' . $mysqli->error);
    }

    $keywords = [];
    while (($row = $rows->fetch_assoc()) !== null) {
        $word = strtolower((string) $row['WORD']);
        $keywords[$word] = (int) $row['RESERVED'] === 1;
    }
    return [$version, $keywords];
}

/**
 * MariaDB's INFORMATION_SCHEMA.KEYWORDS does not expose a RESERVED
 * column. Derive it by probing each word: bare `SELECT <word>` fails
 * with error 1064 (syntax) when the word is reserved, and 1054
 * (unknown column) when it parses as an identifier.
 *
 * @return array{string, array<string, bool>}  [version, [WORD => reserved]]
 */
function dumpMariadbKeywords(mysqli $mysqli): array
{
    $version = scalarQuery($mysqli, 'SELECT VERSION()');

    $rows = $mysqli->query('SELECT WORD FROM INFORMATION_SCHEMA.KEYWORDS ORDER BY WORD');
    if (!$rows instanceof mysqli_result) {
        fail('MariaDB INFORMATION_SCHEMA.KEYWORDS query failed: ' . $mysqli->error);
    }

    $allWords = [];
    while (($row = $rows->fetch_assoc()) !== null) {
        $allWords[] = strtolower((string) $row['WORD']);
    }
    fprintf(STDERR, "Probing %d MariaDB keywords for reserved status...\n", count($allWords));

    $keywords = [];
    foreach ($allWords as $word) {
        // Skip operators and other non-identifier tokens. They'd "fail"
        // with syntax errors but for unrelated reasons.
        if (preg_match('/^[a-z_][a-z0-9_]*$/', $word) !== 1) {
            $keywords[$word] = false;
            continue;
        }

        // `SELECT <word>` triggers a parser path that distinguishes
        // reserved-vs-identifier-position. mysqli's default report mode
        // throws on query failure; catch and read the error code.
        $reserved = false;
        try {
            $result = $mysqli->query(sprintf('SELECT %s', $word));
            if ($result instanceof mysqli_result) {
                $result->close();
            }
        } catch (mysqli_sql_exception $e) {
            $reserved = $e->getCode() === 1064;
        }
        $keywords[$word] = $reserved;
    }
    return [$version, $keywords];
}

function scalarQuery(mysqli $mysqli, string $sql): string
{
    $result = $mysqli->query($sql);
    if (!$result instanceof mysqli_result) {
        fail('Query failed: ' . $sql . ': ' . $mysqli->error);
    }
    $row = $result->fetch_row();
    $result->close();
    return (string) ($row[0] ?? '?');
}

/**
 * @param array<string, bool> $keywords
 */
function writeSnapshot(string $path, string $engine, string $version, array $keywords): void
{
    $lines = [
        sprintf('# %s reserved-word snapshot. Engine version: %s.', $engine, $version),
        '# Captured by bin/refresh-reserved-word-supplement.php on cron.',
        '# Do not edit by hand -- changes here are computed.',
        'WORD' . "\t" . 'RESERVED',
    ];
    foreach ($keywords as $word => $reserved) {
        $lines[] = $word . "\t" . ($reserved ? '1' : '0');
    }
    file_put_contents($path, implode("\n", $lines) . "\n");
    fprintf(STDERR, "Wrote %s (%d entries).\n", $path, count($keywords));
}

/**
 * @param array<string, bool> $keywords
 * @return array<string, true>
 */
function collectReserved(array $keywords): array
{
    $set = [];
    foreach ($keywords as $word => $reserved) {
        if ($reserved) {
            $set[$word] = true;
        }
    }
    return $set;
}

/**
 * @return array<string, true>
 */
function collectLibraryReserved(string $engine): array
{
    $contextsDir = dirname(
        (string) (new ReflectionClass(Context::class))->getFileName()
    ) . '/Contexts';

    $files = glob($contextsDir . '/Context' . $engine . '*.php');
    if (!is_array($files) || $files === []) {
        fail("No phpmyadmin/sql-parser Context{$engine}* files in {$contextsDir}");
    }

    $latestClass = null;
    $latestVersion = -1;
    foreach ($files as $file) {
        if (preg_match('/Context' . preg_quote($engine, '/') . '(\d+)\.php$/', $file, $m) === 1) {
            $version = (int) $m[1];
            if ($version > $latestVersion) {
                $latestVersion = $version;
                $latestClass = 'PhpMyAdmin\\SqlParser\\Contexts\\Context' . $engine . $m[1];
            }
        }
    }

    if ($latestClass === null || !class_exists($latestClass)) {
        fail("Could not resolve a Context class for engine {$engine}");
    }

    /** @var array<string, int> $keywords */
    $keywords = $latestClass::KEYWORDS;
    $reserved = [];
    foreach ($keywords as $word => $flags) {
        if (($flags & Token::FLAG_KEYWORD_RESERVED) !== 0) {
            $reserved[strtolower($word)] = true;
        }
    }
    return $reserved;
}

/**
 * @return list<string>
 */
function readCurrentSupplement(string $file): array
{
    $source = file_get_contents($file);
    if ($source === false) {
        fail("Cannot read {$file}");
    }
    if (preg_match('/private const RESERVED_WORD_SUPPLEMENT = \[(.*?)\];/s', $source, $m) !== 1) {
        fail('Could not locate RESERVED_WORD_SUPPLEMENT in registry file.');
    }

    preg_match_all("/'([a-zA-Z_][a-zA-Z0-9_]*)'/", $m[1], $entries);
    return $entries[1];
}

/**
 * @param list<string> $supplement
 */
function writeSupplement(string $file, array $supplement): void
{
    $source = file_get_contents($file);
    if ($source === false) {
        fail("Cannot read {$file}");
    }

    $block = "private const RESERVED_WORD_SUPPLEMENT = [\n";
    foreach ($supplement as $word) {
        $block .= "        '" . $word . "',\n";
    }
    $block .= '    ];';

    $updated = preg_replace(
        '/private const RESERVED_WORD_SUPPLEMENT = \[.*?\];/s',
        $block,
        $source,
        1,
    );
    if ($updated === null || $updated === $source) {
        fail('Failed to rewrite RESERVED_WORD_SUPPLEMENT block.');
    }

    if (file_put_contents($file, $updated) === false) {
        fail("Cannot write {$file}");
    }
}

function fail(string $message): never
{
    fprintf(STDERR, "ERROR: %s\n", $message);
    exit(1);
}
