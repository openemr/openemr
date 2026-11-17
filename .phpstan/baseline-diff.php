<?php

/**
 * Render a markdown summary comparing two PHPStan baseline directories.
 *
 * Given a "base" baseline directory (typically master) and a "head" baseline
 * directory (typically the PR), counts the total number of suppressed error
 * occurrences per identifier (the sum of each entry's `count` field) and
 * emits a GitHub-flavored markdown report on stdout:
 *
 *   - Identifiers whose occurrence count differs between base and head, as
 *     a table sorted by the absolute delta, descending.
 *   - Identifiers that did not change, collapsed inside a <details> block.
 *   - A totals row (base, head, delta).
 *
 * The baseline files are PHP that assemble `$ignoreErrors = [...]`; this
 * script reads them as text and sums the `count` across entries.
 *
 * Usage:
 *   php .phpstan/baseline-diff.php <base-dir> <head-dir>
 *
 * Intended for the PHPStan Baseline Diff workflow, but runnable locally
 * against any two baseline directories.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php {$argv[0]} <base-dir> <head-dir>\n");
    exit(2);
}

$baseDir = $argv[1];
$headDir = $argv[2];

if (!is_dir($baseDir)) {
    fwrite(STDERR, "Base baseline directory not found: {$baseDir}\n");
    exit(2);
}

if (!is_dir($headDir)) {
    fwrite(STDERR, "Head baseline directory not found: {$headDir}\n");
    exit(2);
}

/**
 * Return the sum of a baseline file's `count` fields, or 0 for a missing file
 * so identifiers that only exist on one side are handled naturally.
 */
function count_occurrences(string $file): int
{
    if (!is_file($file)) {
        return 0;
    }
    // SECURITY: do not `require` or `include` the file, even though it is PHP.
    // The PHPStan Baseline Diff workflow runs this script in a privileged
    // workflow_run job on the baseline artifact a pull request's own PHPStan
    // run uploaded, so a fork PR controls the file's contents. Loading it would
    // run the PR author's code with the job's write token. Reading the
    // generator's `'count' => N,` lines as text means a hostile file can at
    // worst misreport its counts.
    $contents = file_get_contents($file);
    if ($contents === false) {
        throw new RuntimeException("Cannot read baseline file: {$file}");
    }
    preg_match_all("/^    'count' => (\d+),$/m", $contents, $matches);
    return array_sum(array_map(intval(...), $matches[1]));
}

/**
 * @return list<string>
 */
function list_identifiers(string $dir): array
{
    $ids = [];
    foreach (glob($dir . '/*.php') ?: [] as $path) {
        $name = basename($path, '.php');
        // A PR picks these file names, and they're echoed into the PR comment.
        if ($name === 'loader' || preg_match('/^[A-Za-z0-9.-]+$/', $name) !== 1) {
            continue;
        }
        $ids[] = $name;
    }
    return $ids;
}

/**
 * @param array{id: string, base: int, head: int, delta: int} $a
 * @param array{id: string, base: int, head: int, delta: int} $b
 */
function compare_by_abs_delta(array $a, array $b): int
{
    $cmp = abs($b['delta']) <=> abs($a['delta']);
    if ($cmp !== 0) {
        return $cmp;
    }
    return strcmp($a['id'], $b['id']);
}

/**
 * @param array{id: string, base: int, head: int, delta: int} $a
 * @param array{id: string, base: int, head: int, delta: int} $b
 */
function compare_by_id(array $a, array $b): int
{
    return strcmp($a['id'], $b['id']);
}

$identifiers = array_values(array_unique(array_merge(
    list_identifiers($baseDir),
    list_identifiers($headDir),
)));
sort($identifiers);

/** @var list<array{id: string, base: int, head: int, delta: int}> $rows */
$rows = [];
$baseTotal = 0;
$headTotal = 0;
foreach ($identifiers as $id) {
    $base = count_occurrences($baseDir . '/' . $id . '.php');
    $head = count_occurrences($headDir . '/' . $id . '.php');
    $baseTotal += $base;
    $headTotal += $head;
    $rows[] = [
        'id' => $id,
        'base' => $base,
        'head' => $head,
        'delta' => $head - $base,
    ];
}

$changed = array_values(array_filter($rows, static fn(array $r): bool => $r['delta'] !== 0));
$unchanged = array_values(array_filter($rows, static fn(array $r): bool => $r['delta'] === 0));

usort($changed, compare_by_abs_delta(...));
usort($unchanged, compare_by_id(...));

$delta = $headTotal - $baseTotal;

/**
 * Format a signed delta for display ("+3", "-7", "0").
 */
function format_delta(int $n): string
{
    if ($n > 0) {
        return '+' . $n;
    }
    return (string) $n;
}

echo "## PHPStan Baseline Diff\n\n";

if ($changed === []) {
    echo "No baseline changes vs master. ";
    echo "Total suppressed occurrences: **" . number_format($baseTotal) . "** ";
    echo "across " . count($unchanged) . " identifier(s).\n";
    exit(0);
}

echo "| Identifier | Master | PR | Δ |\n";
echo "|---|---:|---:|---:|\n";
foreach ($changed as $row) {
    printf(
        "| `%s` | %s | %s | %s |\n",
        $row['id'],
        number_format($row['base']),
        number_format($row['head']),
        format_delta($row['delta']),
    );
}
printf(
    "| **Total** | **%s** | **%s** | **%s** |\n",
    number_format($baseTotal),
    number_format($headTotal),
    format_delta($delta),
);

echo "\n";
printf(
    "%d identifier(s) changed, %d unchanged.\n",
    count($changed),
    count($unchanged),
);

if ($unchanged !== []) {
    echo "\n<details>\n";
    echo "<summary>Unchanged identifiers (" . count($unchanged) . ")</summary>\n\n";
    echo "| Identifier | Occurrences |\n";
    echo "|---|---:|\n";
    foreach ($unchanged as $row) {
        printf("| `%s` | %s |\n", $row['id'], number_format($row['base']));
    }
    echo "\n</details>\n";
}
