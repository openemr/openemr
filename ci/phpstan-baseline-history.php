#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Manages PHPStan baseline history data.
 *
 * Usage:
 *   php ci/phpstan-baseline-history.php rebuild [since-commit]  - Full rebuild from git history
 *   php ci/phpstan-baseline-history.php append                  - Append current commit (for CI)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$repoRoot = dirname(__DIR__);
$outputFile = $repoRoot . '/docs/phpstan-baseline-history.json';
$metricsScript = __DIR__ . '/phpstan-baseline-metrics.php';

$command = $argv[1] ?? 'help';

switch ($command) {
    case 'rebuild':
        $sinceCommit = $argv[2] ?? '4b6b18875973ab2cf78dcaad35f910cc5f06171f';
        rebuildHistory($repoRoot, $outputFile, $sinceCommit);
        break;

    case 'append':
        appendCurrentCommit($repoRoot, $outputFile, $metricsScript);
        break;

    default:
        fwrite(STDERR, "Usage:\n");
        fwrite(STDERR, "  php ci/phpstan-baseline-history.php rebuild [since-commit]  - Full rebuild\n");
        fwrite(STDERR, "  php ci/phpstan-baseline-history.php append                  - Append current commit\n");
        exit(1);
}

/**
 * Append the current commit's metrics to the history file.
 */
function appendCurrentCommit(string $repoRoot, string $outputFile, string $metricsScript): void
{
    chdir($repoRoot);

    // Get current commit info
    $commitHash = trim(shell_exec('git rev-parse --short=10 HEAD') ?? '');
    $commitDate = trim(shell_exec('git log -1 --format=%aI') ?? '');
    $commitSubject = trim(shell_exec('git log -1 --format=%s') ?? '');

    if (empty($commitHash)) {
        fwrite(STDERR, "Error: Could not get current commit info\n");
        exit(1);
    }

    // Extract metrics
    $metricsJson = shell_exec(sprintf('php %s', escapeshellarg($metricsScript)));
    $metrics = json_decode($metricsJson, true);

    if ($metrics === null) {
        fwrite(STDERR, "Error: Could not extract metrics\n");
        exit(1);
    }

    // Load existing history
    $history = [];
    if (file_exists($outputFile)) {
        $history = json_decode(file_get_contents($outputFile), true) ?? [];
    }

    // Remove categories from all existing entries (only latest gets categories)
    foreach ($history as &$entry) {
        unset($entry['categories']);
    }
    unset($entry);

    // Remove duplicate if this commit already exists
    $history = array_values(array_filter($history, fn($e) => $e['commit'] !== $commitHash));

    // Build categories map (just count, not entries)
    $categories = [];
    foreach ($metrics['categories'] as $name => $data) {
        $categories[$name] = $data['count'];
    }

    // Append new entry
    $history[] = [
        'commit' => $commitHash,
        'date' => $commitDate,
        'subject' => $commitSubject,
        'total_count' => $metrics['total_count'],
        'total_entries' => $metrics['total_entries'],
        'categories' => $categories,
    ];

    // Write back
    file_put_contents($outputFile, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

    fwrite(STDERR, sprintf(
        "Appended %s: %d errors, %d entries\n",
        $commitHash,
        $metrics['total_count'],
        $metrics['total_entries']
    ));
}

/**
 * Rebuild history by iterating through git commits.
 */
function rebuildHistory(string $repoRoot, string $outputFile, string $sinceCommit): void
{
    chdir($repoRoot);

    // Get current branch to restore later
    $currentBranch = trim(shell_exec('git rev-parse --abbrev-ref HEAD') ?? '');
    $currentCommit = trim(shell_exec('git rev-parse HEAD') ?? '');

    // Check for uncommitted changes to tracked files
    $status = shell_exec('git status --porcelain --untracked-files=no');
    if (!empty(trim($status ?? ''))) {
        fwrite(STDERR, "Error: Working directory has uncommitted changes. Please commit or stash first.\n");
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
        if (!preg_match('/^(\S+)\s+(\S+)\s+(.*)$/', $line, $parts)) {
            continue;
        }

        [, $hash, $date, $subject] = $parts;
        $shortHash = substr($hash, 0, 10);

        fwrite(STDERR, sprintf("[%d/%d] %s %s\n", $i + 1, count($commitLines), $shortHash, substr($subject, 0, 50)));

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

    file_put_contents($outputFile, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

    fwrite(STDERR, sprintf("\nWrote %d data points to %s\n", count($history), $outputFile));
}

/**
 * Extract metrics from baseline files at current checkout.
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
            $metrics['categories'][$basename] = $categoryCount;
        }
    }

    return $metrics;
}
