#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Extracts metrics from PHPStan baseline files.
 *
 * Outputs JSON with total error count and per-category breakdown.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$baselineDir = dirname(__DIR__) . '/.phpstan/baseline';

if (!is_dir($baselineDir)) {
    fwrite(STDERR, "Baseline directory not found: $baselineDir\n");
    exit(1);
}

$metrics = [
    'total_count' => 0,
    'total_entries' => 0,
    'categories' => [],
];

$files = glob($baselineDir . '/*.php');
if ($files === false) {
    fwrite(STDERR, "Failed to glob baseline files\n");
    exit(1);
}

foreach ($files as $file) {
    $basename = basename($file, '.php');

    // Skip the loader file
    if ($basename === 'loader') {
        continue;
    }

    $content = file_get_contents($file);
    if ($content === false) {
        fwrite(STDERR, "Failed to read: $file\n");
        continue;
    }

    // Count entries and sum counts
    preg_match_all("/'\s*count'\s*=>\s*(\d+)/", $content, $matches);

    $categoryCount = 0;
    $categoryEntries = count($matches[1]);

    foreach ($matches[1] as $count) {
        $categoryCount += (int) $count;
    }

    $metrics['total_count'] += $categoryCount;
    $metrics['total_entries'] += $categoryEntries;

    if ($categoryEntries > 0) {
        $metrics['categories'][$basename] = [
            'count' => $categoryCount,
            'entries' => $categoryEntries,
        ];
    }
}

// Sort categories by count descending
uasort($metrics['categories'], fn($a, $b) => $b['count'] <=> $a['count']);

echo json_encode($metrics, JSON_PRETTY_PRINT) . "\n";
