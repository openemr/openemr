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

    public function getReadiness(
        string $repo,
        int $number,
        bool $requireApproval = true,
        array $ignoreChecks = [],
        bool $requireNonDraft = true,
    ): PullRequestReadiness {
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

        return new PullRequestReadiness(
            $data['headRefOid'],
            self::reasonsFromPullRequestData($data, $requireApproval, $ignoreChecks, $requireNonDraft),
        );
    }

    /**
     * Pure evaluation of gh's `pr view` data into a list of blocking reasons.
     * Extracted from getReadiness() so the rules can be unit-tested directly
     * without shelling out to `gh`.
     *
     * mergeStateStatus rule: CLEAN passes unconditionally. UNSTABLE
     * ("mergeable, all required checks green, but one or more non-required
     * checks failing") passes IF the status-check rollup evaluation
     * returned no blocking reasons — i.e. the only reason GitHub reports
     * UNSTABLE is check failures the operator has told us to ignore via
     * $ignoreChecks. Every other state (BLOCKED, DIRTY, BEHIND, HAS_HOOKS,
     * UNKNOWN) still blocks because those aren't ignorable: BLOCKED means
     * a REQUIRED check failed or review is missing, DIRTY means merge
     * conflicts, BEHIND means head is behind base, etc.
     *
     * $requireNonDraft gates the isDraft check. Conductor PRs must be non-
     * draft at preflight (they're the meaningful human-review artifact).
     * Docs + Finalize PRs are bot-generated and are AUTO-drafted by their
     * generator workflows, then auto-flipped by post-tag workflows —
     * meaning they're structurally draft at preflight time. Blocking on
     * that would deadlock (drafts don't flip until conductor merges, and
     * conductor won't merge until preflight clears). The full-auto path
     * re-checks readiness after the auto-flip via
     * refreshDownstreamBeforeMerge, so relaxing preflight for downstream
     * targets is safe.
     *
     * @param array{
     *     isDraft: bool,
     *     mergeable: string,
     *     mergeStateStatus: string,
     *     reviewDecision: ?string,
     *     statusCheckRollup: list<array<string, mixed>>,
     *     latestReviews: list<array{state: string, author?: array{login?: string}}>,
     * } $data
     * @param list<string> $ignoreChecks
     * @return list<string>
     */
    public static function reasonsFromPullRequestData(
        array $data,
        bool $requireApproval,
        array $ignoreChecks,
        bool $requireNonDraft = true,
    ): array {
        $reasons = [];
        if ($requireNonDraft && $data['isDraft']) {
            $reasons[] = 'PR is a draft';
        }
        if ($data['mergeable'] !== 'MERGEABLE') {
            $reasons[] = sprintf('mergeable=%s (need MERGEABLE)', $data['mergeable']);
        }

        // Evaluate the status-check rollup up-front so the mergeStateStatus
        // rule below can consult it — UNSTABLE is accepted only when the
        // ignore-list has fully cleared the rollup.
        $checkReasons = self::reasonsFromStatusRollup(
            $data['statusCheckRollup'],
            ShipReleaseOrchestrator::STATUS_CONTEXT,
            $ignoreChecks,
        );

        if (
            $data['mergeStateStatus'] !== 'CLEAN'
            && !($data['mergeStateStatus'] === 'UNSTABLE' && $checkReasons === [])
        ) {
            $reasons[] = sprintf('mergeStateStatus=%s (need CLEAN)', $data['mergeStateStatus']);
        }
        if ($requireApproval && ($data['reviewDecision'] ?? null) !== 'APPROVED') {
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

        return array_merge($reasons, $checkReasons);
    }

    /**
     * Convert gh's statusCheckRollup into a list of blocking reasons.
     *
     * Deduplicates by check name (or legacy status context) first, keeping
     * only the LATEST entry per key. GitHub returns re-run attempts as
     * additional rollup entries — a stale FAILURE from an earlier attempt
     * on the same head SHA would otherwise block preflight even after a
     * successful re-run, because the enumeration would see both. Ordering
     * uses `completedAt` (present on finished check-runs and legacy
     * statuses via `createdAt`), falling back to `startedAt` for in-progress
     * runs, so an in-progress newer attempt correctly wins over an older
     * completed one.
     *
     * Skips any check whose context matches $ownContext — a prior ship-
     * release run may have posted a failure status there, and the
     * orchestrator must not gate itself on its own marker. Also skips any
     * check whose name or context appears in $ignoreChecks (operator-
     * supplied bypass for upstream known-broken jobs).
     *
     * @param  list<array<string, mixed>> $rollup
     * @param  list<string>               $ignoreChecks
     * @return list<string>
     */
    public static function reasonsFromStatusRollup(
        array $rollup,
        string $ownContext,
        array $ignoreChecks = [],
    ): array {
        $ignore = array_flip($ignoreChecks);
        $reasons = [];
        foreach (self::latestPerCheckKey($rollup) as $check) {
            if (($check['context'] ?? null) === $ownContext) {
                continue;
            }
            $name = is_string($check['name'] ?? null) ? $check['name'] : null;
            $context = is_string($check['context'] ?? null) ? $check['context'] : null;
            if (($name !== null && isset($ignore[$name])) || ($context !== null && isset($ignore[$context]))) {
                continue;
            }
            $reasons = array_merge($reasons, self::checkBlockingReason($check));
        }
        return $reasons;
    }

    /**
     * Collapse repeated check entries in the rollup down to the latest per
     * name/context. `gh pr view --json statusCheckRollup` returns every
     * check-run attempt on the head SHA, including re-runs — so an old
     * FAILURE and a fresh SUCCESS both show up. Ordering uses `completedAt`
     * (present on completed check-runs) falling back to `startedAt` (present
     * on in-progress runs). Legacy commit statuses have `createdAt` (also
     * timestamp-shaped and lexicographically ordered), which fits the same
     * ordering rule. Entries without a resolvable timestamp compare as
     * empty string and lose to any timestamped entry, but preserve their
     * insertion-order relative position vs each other.
     *
     * @param  list<array<string, mixed>>  $rollup
     * @return list<array<string, mixed>>
     */
    private static function latestPerCheckKey(array $rollup): array
    {
        /** @var array<string, array{check: array<string, mixed>, ts: string, seq: int}> $latest */
        $latest = [];
        $seq = 0;
        foreach ($rollup as $check) {
            $seq++;
            $name = is_string($check['name'] ?? null) ? $check['name'] : null;
            $context = is_string($check['context'] ?? null) ? $check['context'] : null;
            $key = $name ?? $context;
            if ($key === null) {
                continue;
            }
            $ts = self::checkTimestamp($check);
            if (!isset($latest[$key])) {
                $latest[$key] = ['check' => $check, 'ts' => $ts, 'seq' => $seq];
            } elseif ($ts > $latest[$key]['ts']) {
                // Update check + ts to the newer attempt but preserve the
                // original first-seen seq so downstream ordering stays
                // deterministic. Overwriting seq here would shift a re-run's
                // check to its later position in the rollup, contradicting
                // the "first-seen sequence per key" ordering rule below.
                $latest[$key]['check'] = $check;
                $latest[$key]['ts'] = $ts;
            }
        }
        // Restore original relative order (by first-seen sequence per key) so
        // downstream error messages remain stable across otherwise-equivalent
        // rollups.
        uasort($latest, static fn (array $a, array $b): int => $a['seq'] <=> $b['seq']);
        return array_values(array_map(static fn (array $entry): array => $entry['check'], $latest));
    }

    /**
     * @param array<string, mixed> $check
     */
    private static function checkTimestamp(array $check): string
    {
        foreach (['completedAt', 'startedAt', 'createdAt'] as $field) {
            $value = $check[$field] ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }
        return '';
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

    public function releaseExists(string $repo, string $tag): bool
    {
        $process = new Process([
            'gh', 'release', 'view', $tag,
            '--repo', $repo,
            '--json', 'name',
        ]);
        $process->run();
        if ($process->isSuccessful()) {
            return true;
        }
        // Distinguish the expected "release not found" (return false, keep
        // polling) from genuine gh failures (auth / network / repo not
        // found). Without this the polling loop would spin until timeout
        // on auth-broken runs and surface the misleading "Release object
        // not created" message instead of the real underlying error.
        //
        // gh's stderr for a missing release contains "release not found"
        // (with the tag interpolated). Auth failures surface as "HTTP 401"
        // or "authentication required"; network failures surface with
        // context (host, connection refused). Match the not-found case
        // narrowly and re-throw everything else.
        $stderr = $process->getErrorOutput();
        if (str_contains($stderr, 'release not found')) {
            return false;
        }
        throw new \RuntimeException(sprintf(
            'gh release view %s --repo %s failed (exit %d): %s',
            $tag,
            $repo,
            $process->getExitCode() ?? -1,
            trim($stderr),
        ));
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
