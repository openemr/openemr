#!/usr/bin/env php
<?php

/**
 * Refresh MYSQL_EIGHT_SUPPLEMENT in ReservedWordRegistry.
 *
 * Compares MySQL's authoritative reserved-word list (scraped from the
 * Reference Manual keywords page) against what phpmyadmin/sql-parser ships
 * via its highest-numbered ContextMySql* class, and rewrites the
 * MYSQL_EIGHT_SUPPLEMENT constant to cover the gap.
 *
 * Designed to be invoked from .github/workflows/refresh-mysql-supplement.yml
 * on a monthly cron. The workflow opens a PR if the file changes.
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

const MYSQL_KEYWORDS_URL = 'https://dev.mysql.com/doc/refman/8.4/en/keywords.html';
const REGISTRY_FILE = __DIR__ . '/../tests/PHPStan/Rules/Sql/ReservedWordRegistry.php';

main();

function main(): void
{
    $authoritative = fetchMysqlReserved();
    if ($authoritative === []) {
        fail('No reserved words extracted from MySQL docs -- page format may have changed.');
    }
    fprintf(STDERR, "Fetched %d reserved words from MySQL docs.\n", count($authoritative));

    $library = collectLibraryReserved('MySql');
    fprintf(STDERR, "phpmyadmin/sql-parser ships %d reserved words for MySQL.\n", count($library));

    // Single-word reserved entries that the library does not cover.
    // Composed tokens (e.g. "ORDER BY") are dropped -- they can never be
    // bare column names so they have no place in our supplement.
    $missing = array_diff_key($authoritative, $library);
    $missing = array_filter(
        $missing,
        static fn(string $word): bool => !str_contains($word, ' '),
        ARRAY_FILTER_USE_KEY,
    );

    $supplement = array_keys($missing);
    sort($supplement);
    fprintf(STDERR, "Computed supplement has %d entries.\n", count($supplement));

    $current = readCurrentSupplement(REGISTRY_FILE);
    if ($current === $supplement) {
        fprintf(STDERR, "No drift -- supplement is current.\n");
        return;
    }

    fprintf(STDERR, "Drift detected. Rewriting %s.\n", REGISTRY_FILE);
    writeSupplement(REGISTRY_FILE, $supplement);

    fprintf(STDERR, "Done. Run `git diff` to review.\n");
}

/**
 * @return array<string, true>
 */
function fetchMysqlReserved(): array
{
    if (!function_exists('curl_init')) {
        fail('ext-curl is required.');
    }

    // MySQL's docs CDN occasionally rate-limits or bot-blocks. When that
    // happens the workflow fails loudly and the maintainer either retries
    // or updates MYSQL_EIGHT_SUPPLEMENT by hand from
    // https://dev.mysql.com/doc/refman/8.4/en/keywords.html (look for the
    // (R) markers). This script's job is automation when it works, not a
    // load-bearing source of truth.
    $ch = curl_init(MYSQL_KEYWORDS_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
        CURLOPT_ENCODING => '',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
        ],
    ]);
    $html = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error = curl_error($ch);

    if (!is_string($html) || $html === '' || $status !== 200) {
        fail(sprintf(
            "Failed to fetch %s (HTTP %s, curl error: %s).\n"
            . "MySQL's docs CDN may be rate-limiting. Retry the workflow, or update\n"
            . 'MYSQL_EIGHT_SUPPLEMENT by hand from the keywords page (look for the (R) markers).',
            MYSQL_KEYWORDS_URL,
            $status,
            $error !== '' ? $error : 'none',
        ));
    }

    // Page format: <code class="literal">KEYWORD</code> (R)
    // The (R) marker means reserved. Optional (R; added in 8.0.x) variants
    // are also accepted.
    preg_match_all(
        '#<code class="literal">([A-Za-z_][A-Za-z0-9_]*)</code>\s*\(R[;)]#',
        $html,
        $matches,
    );

    $set = [];
    foreach ($matches[1] as $word) {
        $set[strtolower($word)] = true;
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
    if (preg_match('/private const MYSQL_EIGHT_SUPPLEMENT = \[(.*?)\];/s', $source, $m) !== 1) {
        fail('Could not locate MYSQL_EIGHT_SUPPLEMENT in registry file.');
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

    $block = "private const MYSQL_EIGHT_SUPPLEMENT = [\n";
    foreach ($supplement as $word) {
        $block .= "        '" . $word . "',\n";
    }
    $block .= '    ];';

    $updated = preg_replace(
        '/private const MYSQL_EIGHT_SUPPLEMENT = \[.*?\];/s',
        $block,
        $source,
        1,
    );
    if ($updated === null || $updated === $source) {
        fail('Failed to rewrite MYSQL_EIGHT_SUPPLEMENT block.');
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
