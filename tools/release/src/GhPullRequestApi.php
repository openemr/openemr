<?php

/**
 * gh-CLI implementation of PullRequestApi. Authenticates via the ambient
 * GH_TOKEN env var (the workflow mints an App token and exports it).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Process\Process;

final readonly class GhPullRequestApi implements PullRequestApi
{
    public function findByHead(string $repo, string $branch): ?PullRequestSnapshot
    {
        $process = new Process([
            'gh', 'pr', 'list',
            '--repo', $repo,
            '--head', $branch,
            '--state', 'all',
            '--limit', '1',
            '--json', 'number,headRefOid,baseRefName,state',
        ]);
        $process->mustRun();

        $output = trim($process->getOutput());
        if ($output === '' || $output === '[]') {
            return null;
        }
        /** @var list<array{number: int, headRefOid: string, baseRefName: string, state: string}> $rows */
        $rows = $this->decodeJson($output, "gh pr list for {$repo}/{$branch}");
        if ($rows === []) {
            return null;
        }
        $row = $rows[0];
        return new PullRequestSnapshot(
            $row['number'],
            $row['headRefOid'],
            $row['baseRefName'],
            PullRequestState::from($row['state']),
        );
    }

    public function getReadiness(string $repo, int $number): PullRequestReadiness
    {
        $process = new Process([
            'gh', 'pr', 'view', (string) $number,
            '--repo', $repo,
            '--json', 'isDraft,mergeable,mergeStateStatus,reviewDecision,'
                . 'statusCheckRollup,latestReviews,headRefOid',
        ]);
        $process->mustRun();

        /**
         * @var array{
         *     isDraft: bool,
         *     mergeable: string,
         *     mergeStateStatus: string,
         *     reviewDecision: ?string,
         *     statusCheckRollup: list<array<string, mixed>>,
         *     latestReviews: list<array{state: string, author?: array{login?: string}}>,
         *     headRefOid: string,
         * } $data
         */
        $data = $this->decodeJson(trim($process->getOutput()), "gh pr view {$repo}#{$number}");

        $reasons = [];
        if ($data['isDraft']) {
            $reasons[] = 'PR is a draft';
        }
        if ($data['mergeable'] !== 'MERGEABLE') {
            $reasons[] = sprintf('mergeable=%s (need MERGEABLE)', $data['mergeable']);
        }
        if ($data['mergeStateStatus'] !== 'CLEAN') {
            $reasons[] = sprintf('mergeStateStatus=%s (need CLEAN)', $data['mergeStateStatus']);
        }
        if (($data['reviewDecision'] ?? null) !== 'APPROVED') {
            $reasons[] = sprintf(
                'reviewDecision=%s (need APPROVED)',
                $data['reviewDecision'] ?? 'null',
            );
        }
        foreach ($data['latestReviews'] as $review) {
            if ($review['state'] === 'CHANGES_REQUESTED') {
                $reasons[] = sprintf(
                    'CHANGES_REQUESTED review by %s',
                    $review['author']['login'] ?? 'unknown',
                );
            }
        }
        $reasons = array_merge(
            $reasons,
            self::reasonsFromStatusRollup($data['statusCheckRollup'], ShipReleaseOrchestrator::STATUS_CONTEXT),
        );
        return new PullRequestReadiness($data['headRefOid'], $reasons);
    }

    /**
     * Convert gh's statusCheckRollup into a list of blocking reasons. Skips
     * any check whose context matches $ownContext — a prior ship-release run
     * may have posted a failure status there, and the orchestrator must not
     * gate itself on its own marker.
     *
     * @param  list<array<string, mixed>> $rollup
     * @return list<string>
     */
    public static function reasonsFromStatusRollup(array $rollup, string $ownContext): array
    {
        $reasons = [];
        foreach ($rollup as $check) {
            if (($check['context'] ?? null) === $ownContext) {
                continue;
            }
            $reasons = array_merge($reasons, self::checkBlockingReason($check));
        }
        return $reasons;
    }

    public function postCommitStatus(
        string $repo,
        string $sha,
        string $context,
        string $state,
        string $description,
        string $targetUrl,
    ): void {
        $argv = [
            'gh', 'api',
            "repos/{$repo}/statuses/{$sha}",
            '--method', 'POST',
            '-f', "state={$state}",
            '-f', "context={$context}",
            '-f', "description={$description}",
        ];
        if ($targetUrl !== '') {
            $argv[] = '-f';
            $argv[] = "target_url={$targetUrl}";
        }
        $process = new Process($argv);
        $process->mustRun();
    }

    public function squashMerge(string $repo, int $number, string $expectedHeadSha): string
    {
        // --delete-branch=false is set explicitly so gh doesn't prompt about
        // branch deletion when run from the workflow's non-TTY shell.
        $merge = new Process([
            'gh', 'pr', 'merge', (string) $number,
            '--repo', $repo,
            '--squash',
            '--match-head-commit', $expectedHeadSha,
            '--delete-branch=false',
        ]);
        $merge->setTimeout(300.0);
        $merge->mustRun();

        // Best-effort fetch of the resulting merge commit SHA for the report.
        // Failure here doesn't roll back the merge — the merge succeeded if
        // mustRun() above didn't throw — so swallow the error and report a
        // sentinel rather than failing the whole orchestration.
        try {
            $view = new Process([
                'gh', 'pr', 'view', (string) $number,
                '--repo', $repo,
                '--json', 'mergeCommit',
                '--jq', '.mergeCommit.oid // ""',
            ]);
            $view->mustRun();
            $sha = trim($view->getOutput());
        } catch (\RuntimeException) {
            return '<merge-sha-unavailable>';
        }
        return $sha === '' ? '<merge-sha-unavailable>' : $sha;
    }

    /**
     * Decode JSON output from gh, raising a controlled error with the
     * originating context instead of the bare PHP TypeError that follows
     * indexing into a null result.
     */
    private function decodeJson(string $payload, string $context): mixed
    {
        try {
            return json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException(
                "Failed to decode JSON from {$context}: {$e->getMessage()}",
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @param array<string, mixed> $check
     * @return list<string>
     */
    private static function checkBlockingReason(array $check): array
    {
        $name = is_string($check['name'] ?? null) ? $check['name'] : 'unknown';
        $context = is_string($check['context'] ?? null) ? $check['context'] : $name;

        // Check runs use status/conclusion (conclusion may be null while in
        // progress, so use array_key_exists, not isset). Legacy commit
        // statuses use state.
        if (array_key_exists('status', $check)) {
            $status = is_string($check['status']) ? $check['status'] : '';
            $conclusion = is_string($check['conclusion'] ?? null) ? $check['conclusion'] : '';
            if ($status !== 'COMPLETED') {
                return [sprintf('check %s status=%s (need COMPLETED)', $name, $status)];
            }
            if (!in_array($conclusion, ['SUCCESS', 'NEUTRAL', 'SKIPPED'], true)) {
                return [sprintf('check %s conclusion=%s', $name, $conclusion)];
            }
            return [];
        }
        if (isset($check['state'])) {
            // Legacy commit-status states: SUCCESS / FAILURE / ERROR / PENDING / EXPECTED.
            // EXPECTED means "this status is expected but hasn't been reported yet" — treat
            // it as blocking, same as PENDING. Only SUCCESS clears the gate.
            $state = is_string($check['state']) ? $check['state'] : '';
            if ($state !== 'SUCCESS') {
                return [sprintf('status %s state=%s', $context, $state)];
            }
        }
        return [];
    }
}
