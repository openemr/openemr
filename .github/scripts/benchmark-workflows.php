#!/usr/bin/env php
<?php

/**
 * Benchmark GitHub Actions workflow runtimes between two branches.
 *
 * Discovers all workflows that ran on either branch, fetches job timing data
 * in parallel using curl_multi, and outputs a Markdown comparison table with
 * links to each run.
 *
 * Usage: php benchmark-workflows.php <head-branch> <base-branch>
 *
 * Required env: GH_TOKEN (or GITHUB_TOKEN), GITHUB_REPOSITORY
 * Optional env: GITHUB_SERVER_URL (defaults to https://github.com)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$head = $argv[1] ?? null;
$base = $argv[2] ?? null;
$repo = getenv('GITHUB_REPOSITORY') ?: '';
$token = getenv('GH_TOKEN') ?: getenv('GITHUB_TOKEN') ?: '';
$serverUrl = rtrim(getenv('GITHUB_SERVER_URL') ?: 'https://github.com', '/');

if (!$head || !$base) {
    fwrite(STDERR, "Usage: php {$argv[0]} <head-branch> <base-branch>\n");
    fwrite(STDERR, "Required env: GITHUB_REPOSITORY, GH_TOKEN (or GITHUB_TOKEN)\n");
    exit(1);
}

if ($repo === '' || $token === '') {
    fwrite(STDERR, "Error: GITHUB_REPOSITORY and GH_TOKEN env vars are required\n");
    exit(1);
}

$apiBase = 'https://api.github.com';

/**
 * Execute multiple HTTP GET requests in parallel using curl_multi.
 *
 * @param array<string, string> $urls Map of key => URL
 * @return array<string, ?array> Map of key => decoded JSON (null on failure)
 */
function fetchParallel(array $urls, string $token): array
{
    if (empty($urls)) {
        return [];
    }

    $mh = curl_multi_init();
    $handles = [];
    $headers = [
        "Authorization: Bearer $token",
        'Accept: application/vnd.github+json',
        'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent: benchmark-workflows',
    ];

    foreach ($urls as $key => $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $handles[$key] = $ch;
        curl_multi_add_handle($mh, $ch);
    }

    do {
        $status = curl_multi_exec($mh, $active);
        if ($active) {
            curl_multi_select($mh);
        }
    } while ($active && $status === CURLM_OK);

    $results = [];
    foreach ($handles as $key => $ch) {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = curl_multi_getcontent($ch);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
        $results[$key] = ($httpCode === 200) ? json_decode($body, true) : null;
    }

    curl_multi_close($mh);
    return $results;
}

/**
 * Group workflow runs by name, keeping only the most recent successful run.
 * The API returns runs sorted by created_at desc, so first match per name wins.
 *
 * @return array<string, array> Map of workflow name => run object
 */
function groupByWorkflow(?array $apiResult): array
{
    if (!$apiResult) {
        return [];
    }
    $grouped = [];
    foreach ($apiResult['workflow_runs'] ?? [] as $run) {
        $name = $run['name'];
        if (!isset($grouped[$name])) {
            $grouped[$name] = $run;
        }
    }
    return $grouped;
}

/**
 * Compute total job execution time (sum of individual job durations).
 */
function computeDuration(?array $jobsResult): ?int
{
    if (!$jobsResult || empty($jobsResult['jobs'])) {
        return null;
    }
    $total = 0;
    $counted = false;
    foreach ($jobsResult['jobs'] as $job) {
        if (empty($job['completed_at']) || empty($job['started_at'])) {
            continue;
        }
        $total += strtotime($job['completed_at']) - strtotime($job['started_at']);
        $counted = true;
    }
    return $counted ? $total : null;
}

/**
 * Format seconds as human-readable duration.
 */
function formatDuration(int $seconds): string
{
    if ($seconds < 60) {
        return "{$seconds}s";
    }
    $m = intdiv($seconds, 60);
    $s = $seconds % 60;
    return "{$m}m {$s}s";
}

// --- Main ---

// Step 1: Fetch successful runs for both branches in parallel
fwrite(STDERR, "Fetching workflow runs for $head and $base...\n");

$runsData = fetchParallel([
    'head' => "$apiBase/repos/$repo/actions/runs?" . http_build_query([
        'branch' => $head,
        'status' => 'success',
        'per_page' => 100,
    ]),
    'base' => "$apiBase/repos/$repo/actions/runs?" . http_build_query([
        'branch' => $base,
        'status' => 'success',
        'per_page' => 100,
    ]),
], $token);

$headRuns = groupByWorkflow($runsData['head']);
$baseRuns = groupByWorkflow($runsData['base']);

// Union of all workflow names from both branches
$allWorkflows = array_unique(array_merge(array_keys($headRuns), array_keys($baseRuns)));
sort($allWorkflows);

if (empty($allWorkflows)) {
    echo "No successful workflow runs found on either branch.\n";
    exit(0);
}

// Step 2: Fetch job timing details for all relevant runs in parallel
$jobUrls = [];
foreach ($allWorkflows as $name) {
    if (isset($headRuns[$name])) {
        $jobUrls["head:$name"] = "$apiBase/repos/$repo/actions/runs/{$headRuns[$name]['id']}/jobs";
    }
    if (isset($baseRuns[$name])) {
        $jobUrls["base:$name"] = "$apiBase/repos/$repo/actions/runs/{$baseRuns[$name]['id']}/jobs";
    }
}

fwrite(STDERR, sprintf("Fetching job details for %d runs...\n", count($jobUrls)));
$jobResults = fetchParallel($jobUrls, $token);

// Step 3: Build and output markdown table
echo "| Workflow | $head | $base | Delta |\n";
echo "|----------|-------|-------|-------|\n";

foreach ($allWorkflows as $name) {
    $headRun = $headRuns[$name] ?? null;
    $baseRun = $baseRuns[$name] ?? null;
    $headDuration = computeDuration($jobResults["head:$name"] ?? null);
    $baseDuration = computeDuration($jobResults["base:$name"] ?? null);

    // Head cell
    if ($headRun !== null && $headDuration !== null) {
        $url = "$serverUrl/$repo/actions/runs/{$headRun['id']}";
        $headCell = sprintf('[%s](%s)', formatDuration($headDuration), $url);
    } elseif ($headRun !== null) {
        $url = "$serverUrl/$repo/actions/runs/{$headRun['id']}";
        $headCell = "[err]($url)";
    } else {
        $headCell = 'skipped';
    }

    // Base cell
    if ($baseRun !== null && $baseDuration !== null) {
        $url = "$serverUrl/$repo/actions/runs/{$baseRun['id']}";
        $baseCell = sprintf('[%s](%s)', formatDuration($baseDuration), $url);
    } elseif ($baseRun !== null) {
        $url = "$serverUrl/$repo/actions/runs/{$baseRun['id']}";
        $baseCell = "[err]($url)";
    } else {
        $baseCell = 'no baseline';
    }

    // Delta cell
    if ($headDuration !== null && $baseDuration !== null) {
        $delta = $headDuration - $baseDuration;
        if ($delta > 0) {
            $deltaCell = '+' . formatDuration($delta);
        } elseif ($delta < 0) {
            $deltaCell = '-' . formatDuration(abs($delta));
        } else {
            $deltaCell = '0s';
        }
    } else {
        $deltaCell = '-';
    }

    echo "| $name | $headCell | $baseCell | $deltaCell |\n";
}
