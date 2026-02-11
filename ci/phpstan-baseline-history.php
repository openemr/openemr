#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Generates historical PHPStan baseline metrics by iterating through git commits.
 *
 * Usage: php ci/phpstan-baseline-history.php [since-commit]
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$repoRoot = dirname(__DIR__);
$outputFile = $repoRoot . '/docs/phpstan-baseline-history.json';

// Default: commit where baseline format changed
$sinceCommit = $argv[1] ?? '4b6b18875973ab2cf78dcaad35f910cc5f06171f';

chdir($repoRoot);

// Get current branch to restore later
$currentBranch = trim(shell_exec('git rev-parse --abbrev-ref HEAD') ?? '');
$currentCommit = trim(shell_exec('git rev-parse HEAD') ?? '');

// Check for uncommitted changes to tracked files (untracked files are fine)
$status = shell_exec('git status --porcelain --untracked-files=no');
if (!empty(trim($status ?? ''))) {
    fwrite(STDERR, "Error: Working directory has uncommitted changes to tracked files. Please commit or stash first.\n");
    exit(1);
}

// Get list of commits that touched the baseline (oldest first)
$cmd = sprintf(
    'git log --reverse --format="%%H %%aI %%s" %s^..HEAD -- .phpstan/baseline/',
    escapeshellarg($sinceCommit)
);
$commitLines = array_filter(explode("\n", shell_exec($cmd) ?? ''));

if (empty($commitLines)) {
    fwrite(STDERR, "No commits found since $sinceCommit\n");
    exit(1);
}

fwrite(STDERR, sprintf("Processing %d commits...\n", count($commitLines)));

$history = [];

foreach ($commitLines as $i => $line) {
    // Format: hash date subject (subject may contain spaces)
    if (!preg_match('/^(\S+)\s+(\S+)\s+(.*)$/', $line, $parts)) {
        continue;
    }

    [, $hash, $date, $subject] = $parts;
    $shortHash = substr($hash, 0, 10);

    fwrite(STDERR, sprintf("[%d/%d] %s %s\n", $i + 1, count($commitLines), $shortHash, substr($subject, 0, 50)));

    // Checkout the commit
    shell_exec(sprintf('git checkout --quiet %s', escapeshellarg($hash)));

    $metrics = extractMetrics($repoRoot);

    if ($metrics !== null) {
        $entry = [
            'commit' => $shortHash,
            'date' => $date,
            'subject' => $subject,
            'total_count' => $metrics['total_count'],
            'total_entries' => $metrics['total_entries'],
        ];

        // Only include categories for the last commit (will be updated below)
        $entry['_categories'] = $metrics['categories'];

        $history[] = $entry;
    }
}

// Restore original state
if ($currentBranch !== 'HEAD') {
    shell_exec(sprintf('git checkout --quiet %s', escapeshellarg($currentBranch)));
} else {
    shell_exec(sprintf('git checkout --quiet %s', escapeshellarg($currentCommit)));
}

// Only keep categories for the latest entry
if (!empty($history)) {
    $lastIdx = count($history) - 1;
    $history[$lastIdx]['categories'] = $history[$lastIdx]['_categories'];
    foreach ($history as &$entry) {
        unset($entry['_categories']);
    }
}

// Ensure output directory exists
$outputDir = dirname($outputFile);
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Write history
file_put_contents($outputFile, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

fwrite(STDERR, sprintf("\nWrote %d data points to %s\n", count($history), $outputFile));

/**
 * Extract metrics from baseline files at current checkout
 */
function extractMetrics(string $repoRoot): ?array
{
    $baselineDir = $repoRoot . '/.phpstan/baseline';

    if (!is_dir($baselineDir)) {
        return null;
    }

    $metrics = [
        'total_count' => 0,
        'total_entries' => 0,
        'categories' => [],
    ];

    $files = glob($baselineDir . '/*.php');
    if ($files === false || empty($files)) {
        return null;
    }

    foreach ($files as $file) {
        $basename = basename($file, '.php');

        if ($basename === 'loader') {
            continue;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            continue;
        }

        preg_match_all("/'\s*count'\s*=>\s*(\d+)/", $content, $matches);

        $categoryCount = 0;
        $categoryEntries = count($matches[1]);

        foreach ($matches[1] as $count) {
            $categoryCount += (int) $count;
        }

        $metrics['total_count'] += $categoryCount;
        $metrics['total_entries'] += $categoryEntries;

        if ($categoryEntries > 0) {
            // Only store count, not entries (slimmer format)
            $metrics['categories'][$basename] = $categoryCount;
        }
    }

    return $metrics;
}
