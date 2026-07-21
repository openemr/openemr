<?php

/**
 * In-memory PullRequestApi for orchestrator tests.
 *
 * Readiness and merge SHAs are keyed by "repo#number" so PR-number collisions
 * across repos (entirely possible — PR numbers are per-repo on GitHub) don't
 * silently mask cross-repo bugs in the orchestrator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release\Fakes;

use OpenEMR\Release\PullRequestApi;
use OpenEMR\Release\PullRequestReadiness;
use OpenEMR\Release\PullRequestSnapshot;

class FakePullRequestApi implements PullRequestApi
{
    /** @var array<string, ?PullRequestSnapshot> */
    private array $snapshotsByKey = [];

    /** @var array<string, PullRequestReadiness> keyed by "repo#number" */
    private array $readinessByPr = [];

    /** @var array<string, list<PullRequestReadiness>> per-PR queue, consumed left-to-right */
    private array $readinessQueue = [];

    /** @var array<string, string> merge SHAs by "repo#number" */
    private array $mergeShas = [];

    /** @var list<array{repo: string, sha: string, context: string, state: string, description: string, targetUrl: string}> */
    public array $postedStatuses = [];

    /** @var list<array{repo: string, number: int, expected: string}> */
    public array $merges = [];

    /** @var array<string, PullRequestSnapshot> snapshots installed by setSnapshotAfterFinds() */
    private array $snapshotAfterFind = [];

    /** @var array<string, int> */
    private array $findCalls = [];

    /** @var array<string, bool> release-existence flags by "repo|tag" */
    private array $releaseExistsByKey = [];

    /** @var array<string, int> "repo|tag" => number of successful checks required before returning true */
    private array $releaseExistsAfterCalls = [];

    /** @var array<string, int> */
    private array $releaseExistsCalls = [];

    /** @var list<array{repo: string, number: int, requireApproval: bool}> */
    public array $readinessCalls = [];

    public function setSnapshot(string $repo, string $branch, ?PullRequestSnapshot $snapshot): void
    {
        $this->snapshotsByKey[$this->branchKey($repo, $branch)] = $snapshot;
    }

    /**
     * After the Nth call to findByHead for this repo+branch, swap to a new snapshot.
     * Used to simulate the docs PR being re-rendered by the conductor merge.
     */
    public function setSnapshotAfterFinds(
        string $repo,
        string $branch,
        int $afterNCalls,
        PullRequestSnapshot $snapshot,
    ): void {
        $this->snapshotAfterFind[$this->branchKey($repo, $branch) . '|' . $afterNCalls] = $snapshot;
    }

    public function setReadiness(string $repo, int $number, PullRequestReadiness $readiness): void
    {
        $this->readinessByPr[$this->prKey($repo, $number)] = $readiness;
    }

    /**
     * Each call to getReadiness() for $repo#$number consumes one entry. Once exhausted,
     * the last entry is returned for subsequent calls.
     *
     * @param list<PullRequestReadiness> $sequence
     */
    public function setReadinessSequence(string $repo, int $number, array $sequence): void
    {
        $this->readinessQueue[$this->prKey($repo, $number)] = $sequence;
    }

    public function setMergeSha(string $repo, int $number, string $sha): void
    {
        $this->mergeShas[$this->prKey($repo, $number)] = $sha;
    }

    public function setReleaseExists(string $repo, string $tag, bool $exists): void
    {
        $this->releaseExistsByKey[$this->releaseKey($repo, $tag)] = $exists;
    }

    /**
     * Return false for the first $afterNCalls invocations of releaseExists()
     * on $repo+$tag, then true forever after. Used to simulate the release
     * object appearing partway through the orchestrator's polling loop.
     */
    public function setReleaseExistsAfterCalls(string $repo, string $tag, int $afterNCalls): void
    {
        $this->releaseExistsAfterCalls[$this->releaseKey($repo, $tag)] = $afterNCalls;
    }

    public function findByHead(string $repo, string $branch): ?PullRequestSnapshot
    {
        $key = $this->branchKey($repo, $branch);
        $this->findCalls[$key] = ($this->findCalls[$key] ?? 0) + 1;
        $swap = $this->snapshotAfterFind[$key . '|' . $this->findCalls[$key]] ?? null;
        if ($swap !== null) {
            $this->snapshotsByKey[$key] = $swap;
        }
        return $this->snapshotsByKey[$key] ?? null;
    }

    public function getReadiness(string $repo, int $number, bool $requireApproval = true): PullRequestReadiness
    {
        $this->readinessCalls[] = ['repo' => $repo, 'number' => $number, 'requireApproval' => $requireApproval];
        $key = $this->prKey($repo, $number);
        if (isset($this->readinessQueue[$key]) && $this->readinessQueue[$key] !== []) {
            $next = array_shift($this->readinessQueue[$key]);
            $this->readinessByPr[$key] = $next;
            return $next;
        }
        if (!isset($this->readinessByPr[$key])) {
            throw new \RuntimeException("No readiness configured for {$key}");
        }
        return $this->readinessByPr[$key];
    }

    public function releaseExists(string $repo, string $tag): bool
    {
        $key = $this->releaseKey($repo, $tag);
        $this->releaseExistsCalls[$key] = ($this->releaseExistsCalls[$key] ?? 0) + 1;
        if (isset($this->releaseExistsAfterCalls[$key])) {
            return $this->releaseExistsCalls[$key] > $this->releaseExistsAfterCalls[$key];
        }
        return $this->releaseExistsByKey[$key] ?? false;
    }

    public function getReleaseExistsCallCount(string $repo, string $tag): int
    {
        return $this->releaseExistsCalls[$this->releaseKey($repo, $tag)] ?? 0;
    }

    public function postCommitStatus(
        string $repo,
        string $sha,
        string $context,
        string $state,
        string $description,
        string $targetUrl,
    ): void {
        $this->postedStatuses[] = [
            'repo' => $repo,
            'sha' => $sha,
            'context' => $context,
            'state' => $state,
            'description' => $description,
            'targetUrl' => $targetUrl,
        ];
    }

    public function squashMerge(string $repo, int $number, string $expectedHeadSha): string
    {
        $this->merges[] = ['repo' => $repo, 'number' => $number, 'expected' => $expectedHeadSha];
        return $this->mergeShas[$this->prKey($repo, $number)] ?? "merge-sha-{$number}";
    }

    private function branchKey(string $repo, string $branch): string
    {
        return $repo . '|' . $branch;
    }

    private function prKey(string $repo, int $number): string
    {
        return $repo . '#' . $number;
    }

    private function releaseKey(string $repo, string $tag): string
    {
        return $repo . '|' . $tag;
    }
}
