<?php

/**
 * Merges the two release PRs (conductor → docs) in strict order.
 *
 * Two-phase: a preflight pass evaluates every unmerged target's readiness and
 * refuses to merge anything if any unmerged PR is not ready (issue #705 step
 * 3 + 5: "no partial merges from the workflow itself"). PRs already merged
 * are skipped so the same trigger handles partial-merge recovery from outside
 * causes (e.g. an admin-overridden direct merge).
 *
 * Detects the one unrecoverable case — docs merged before conductor — and
 * refuses to do anything; that recovery is documented in the runbook.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class ShipReleaseOrchestrator
{
    public const STATUS_CONTEXT = 'release/ship-approved';
    public const STATUS_DESCRIPTION = 'Approved by ship-release workflow';
    private const POLL_INTERVAL_SECONDS = 15;
    private const STATUS_DESCRIPTION_MAX = 140;

    public function __construct(
        private PullRequestApi $api,
        private Clock $clock,
        private int $downstreamTimeoutSeconds = 600,
        private bool $dryRun = false,
        private string $statusTargetUrl = '',
    ) {
    }

    /**
     * @param list<PullRequestTarget> $targets conductor, docs (any order — sorted internally)
     */
    public function ship(array $targets): ShipReleaseResult
    {
        $targets = $this->sortByMergeOrder($targets);
        $snapshots = $this->snapshotAll($targets);

        $fatal = $this->detectDocsFirst($targets, $snapshots);
        if ($fatal !== null) {
            return new ShipReleaseResult($this->markAllNotReached($targets), $fatal);
        }

        // Preflight: evaluate every unmerged target before any merge so a later
        // blocker can't cause earlier ones to ship as a partial merge.
        $preflight = $this->preflight($targets, $snapshots);
        if ($preflight['hasBlocker']) {
            return new ShipReleaseResult($preflight['steps']);
        }

        if ($this->dryRun) {
            return new ShipReleaseResult($this->dryRunSteps($targets, $snapshots));
        }

        return new ShipReleaseResult($this->executeMerges($targets, $snapshots, $preflight['readiness']));
    }

    /**
     * Defensive sort + contract check. The 2-PR ship contract requires
     * exactly the Conductor and Docs targets in any order; this method
     * fails fast on a stale caller passing the wrong shape (e.g., an
     * extra target, a missing one, or duplicate mergeOrder values),
     * since the alternative is a silent half-failure deep in the
     * downstream merge logic.
     *
     * @param  list<PullRequestTarget> $targets
     * @return list<PullRequestTarget>
     */
    private function sortByMergeOrder(array $targets): array
    {
        if (count($targets) !== 2) {
            throw new \LogicException(
                'ship-release expects exactly 2 targets (Conductor + Docs); got ' . count($targets),
            );
        }
        $roles = array_map(static fn (PullRequestTarget $t): string => $t->roleLabel->value, $targets);
        sort($roles);
        $expected = [RoleLabel::Conductor->value, RoleLabel::Docs->value];
        sort($expected);
        if ($roles !== $expected) {
            throw new \LogicException(
                'ship-release targets must be {Conductor, Docs}; got {' . implode(', ', $roles) . '}',
            );
        }
        $orders = array_map(static fn (PullRequestTarget $t): int => $t->mergeOrder, $targets);
        if (count(array_unique($orders)) !== count($orders)) {
            throw new \LogicException('ship-release targets have duplicate mergeOrder values');
        }
        // Enforce Conductor-before-Docs at the mergeOrder level too —
        // {Conductor, Docs} with Docs.mergeOrder < Conductor.mergeOrder
        // would pass the role-set + dedup checks above but usort would
        // then merge Docs first, violating the strict merge sequence.
        $conductor = $this->findRequired($targets, RoleLabel::Conductor);
        $docs = $this->findRequired($targets, RoleLabel::Docs);
        if ($conductor->mergeOrder >= $docs->mergeOrder) {
            throw new \LogicException(
                'ship-release mergeOrder must put Conductor before Docs; got Conductor='
                . $conductor->mergeOrder . ' Docs=' . $docs->mergeOrder,
            );
        }
        usort(
            $targets,
            static fn (PullRequestTarget $a, PullRequestTarget $b): int => $a->mergeOrder <=> $b->mergeOrder,
        );
        return $targets;
    }

    /**
     * @param  list<PullRequestTarget> $targets
     * @return array<string, ?PullRequestSnapshot>
     */
    private function snapshotAll(array $targets): array
    {
        $out = [];
        foreach ($targets as $target) {
            $out[$target->roleLabel->value] = $this->api->findByHead($target->repo, $target->branch);
        }
        return $out;
    }

    /**
     * Probe every unmerged target's readiness. If any is missing or blocked,
     * return per-target step results with no merges performed.
     *
     * @param  list<PullRequestTarget>             $targets
     * @param  array<string, ?PullRequestSnapshot> $snapshots
     * @return array{
     *     hasBlocker: bool,
     *     steps: list<ShipReleaseStepResult>,
     *     readiness: array<string, PullRequestReadiness>,
     * }
     */
    private function preflight(array $targets, array $snapshots): array
    {
        $readiness = [];
        $blocked = [];
        foreach ($targets as $target) {
            $key = $target->roleLabel->value;
            $snapshot = $snapshots[$key] ?? null;
            if ($snapshot === null) {
                $blocked[$key] = ['no PR found for branch ' . $target->branch];
                continue;
            }
            if ($snapshot->isClosed()) {
                $blocked[$key] = [sprintf(
                    'PR #%d is CLOSED without being merged — refusing to ship a closed PR',
                    $snapshot->number,
                )];
                continue;
            }
            if ($snapshot->baseRefName !== $target->expectedBase) {
                $blocked[$key] = [sprintf(
                    'PR base is %s, expected %s — refusing to merge a PR opened against the wrong base',
                    $snapshot->baseRefName,
                    $target->expectedBase,
                )];
                continue;
            }
            if ($snapshot->isMerged()) {
                continue;
            }
            $check = $this->api->getReadiness($target->repo, $snapshot->number);
            $readiness[$key] = $check;
            if (!$check->isReady()) {
                $blocked[$key] = $check->blockingReasons;
            }
        }

        $steps = [];
        foreach ($targets as $target) {
            $key = $target->roleLabel->value;
            $snapshot = $snapshots[$key] ?? null;
            if ($snapshot !== null && $snapshot->isMerged()) {
                $steps[] = new ShipReleaseStepResult(
                    $target,
                    ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED,
                    $snapshot->number,
                    null,
                    [],
                );
                continue;
            }
            if (isset($blocked[$key])) {
                $steps[] = new ShipReleaseStepResult(
                    $target,
                    ShipReleaseStepStatus::BLOCKED,
                    $snapshot?->number,
                    null,
                    $blocked[$key],
                );
                continue;
            }
            // Ready, but preflight failed elsewhere — we won't merge it now.
            if ($blocked !== []) {
                $steps[] = new ShipReleaseStepResult(
                    $target,
                    ShipReleaseStepStatus::NOT_REACHED,
                    $snapshot?->number,
                    null,
                    ['preflight blocker on another PR — no merges performed'],
                );
            }
        }

        return ['hasBlocker' => $blocked !== [], 'steps' => $steps, 'readiness' => $readiness];
    }

    /**
     * Build the dry-run report — preflight already passed, so each unmerged
     * target is "would merge" and merged ones stay "skipped".
     *
     * @param  list<PullRequestTarget>             $targets
     * @param  array<string, ?PullRequestSnapshot> $snapshots
     * @return list<ShipReleaseStepResult>
     */
    private function dryRunSteps(array $targets, array $snapshots): array
    {
        $steps = [];
        foreach ($targets as $target) {
            $snapshot = $snapshots[$target->roleLabel->value] ?? null;
            if ($snapshot !== null && $snapshot->isMerged()) {
                $steps[] = new ShipReleaseStepResult(
                    $target,
                    ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED,
                    $snapshot->number,
                    null,
                    [],
                );
                continue;
            }
            $steps[] = new ShipReleaseStepResult(
                $target,
                ShipReleaseStepStatus::WOULD_MERGE,
                $snapshot?->number,
                null,
                [],
            );
        }
        return $steps;
    }

    /**
     * Real merge pass. Preflight has already validated that every unmerged
     * target was ready at snapshot time. The docs PR gets a fresh readiness
     * check after the conductor's downstream effect re-renders it.
     *
     * @param  list<PullRequestTarget>             $targets
     * @param  array<string, ?PullRequestSnapshot> $snapshots
     * @param  array<string, PullRequestReadiness> $readiness  preflight readiness, by role
     * @return list<ShipReleaseStepResult>
     */
    private function executeMerges(array $targets, array $snapshots, array $readiness): array
    {
        $steps = [];
        $stopReason = null;
        $mergedThisRun = [];

        foreach ($targets as $target) {
            if ($stopReason !== null) {
                $steps[] = $this->notReachedStep($target, $stopReason);
                continue;
            }

            $snapshot = $snapshots[$target->roleLabel->value] ?? null;
            // Preflight already filtered missing PRs.
            if ($snapshot === null) {
                $steps[] = $this->blockedStep($target, null, ['no PR found for branch ' . $target->branch]);
                $stopReason = sprintf('%s PR is missing', $target->roleLabel->value);
                continue;
            }
            if ($snapshot->isMerged()) {
                $steps[] = new ShipReleaseStepResult(
                    $target,
                    ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED,
                    $snapshot->number,
                    null,
                    [],
                );
                continue;
            }

            $stepReadiness = $readiness[$target->roleLabel->value] ?? null;
            if ($target->roleLabel === RoleLabel::Docs) {
                $refresh = $this->refreshDocsBeforeMerge($target, $snapshot, $snapshots, $mergedThisRun);
                if ($refresh instanceof DocsRefreshResult) {
                    if (!$refresh->isSuccess()) {
                        $steps[] = $this->blockedStep($target, $refresh->snapshot?->number, $refresh->blockingReasons);
                        $stopReason = $refresh->stopReason;
                        continue;
                    }
                    $snapshot = $refresh->snapshot;
                    $stepReadiness = $refresh->readiness;
                }
            }

            if ($snapshot === null || $stepReadiness === null) {
                throw new \LogicException(
                    "ship-release: missing snapshot or readiness for {$target->roleLabel->value}",
                );
            }

            try {
                $this->api->postCommitStatus(
                    $target->repo,
                    $stepReadiness->headRefOid,
                    self::STATUS_CONTEXT,
                    'success',
                    self::STATUS_DESCRIPTION,
                    $this->statusTargetUrl,
                );
                $mergeSha = $this->api->squashMerge($target->repo, $snapshot->number, $stepReadiness->headRefOid);
            } catch (\RuntimeException $e) {
                // Best-effort: clear the success status we just posted so a failed
                // run doesn't leave a release/ship-approved=success on the PR head
                // that branch protection might honor for a subsequent manual merge.
                $this->retractApprovalStatus($target->repo, $stepReadiness->headRefOid, $e->getMessage());
                $steps[] = $this->blockedStep(
                    $target,
                    $snapshot->number,
                    [sprintf('gh call failed: %s', $e->getMessage())],
                );
                $stopReason = sprintf('%s merge failed', $target->roleLabel->value);
                continue;
            }
            $steps[] = new ShipReleaseStepResult(
                $target,
                ShipReleaseStepStatus::MERGED,
                $snapshot->number,
                $mergeSha,
                [],
            );
            $mergedThisRun[] = $target->roleLabel;
        }

        return $steps;
    }

    /**
     * @param list<PullRequestTarget>             $targets
     * @param array<string, ?PullRequestSnapshot> $snapshots
     */
    private function detectDocsFirst(array $targets, array $snapshots): ?string
    {
        $docs = $snapshots[RoleLabel::Docs->value] ?? null;
        $conductor = $snapshots[RoleLabel::Conductor->value] ?? null;
        if ($docs === null || !$docs->isMerged()) {
            return null;
        }
        if ($conductor !== null && $conductor->isMerged()) {
            return null;
        }
        $conductorTarget = $this->findRequired($targets, RoleLabel::Conductor);
        $docsTarget = $this->findRequired($targets, RoleLabel::Docs);
        return sprintf(
            'docs PR (%s#%d, branch %s) was merged before conductor PR (%s, branch %s).'
            . ' This is the unrecoverable docs-first case from issue #705 — the docs page'
            . ' shipped FINAL with no tag. See the release runbook for manual reconciliation.',
            $docsTarget->repo,
            $docs->number,
            $docsTarget->branch,
            $conductorTarget->repo,
            $conductorTarget->branch,
        );
    }

    /**
     * @param list<PullRequestTarget> $targets
     */
    private function findRequired(array $targets, RoleLabel $role): PullRequestTarget
    {
        foreach ($targets as $target) {
            if ($target->roleLabel === $role) {
                return $target;
            }
        }
        throw new \LogicException("ship-release targets list is missing role: {$role->value}");
    }

    /**
     * Two cases need a fresh state read before merging docs:
     *   - conductor merged in *this* run: poll until head SHA flips (or time
     *     out), then re-check readiness against the new SHA.
     *   - conductor was already merged when we started (recovery case): re-check
     *     readiness right now in case the previous run's downstream re-render
     *     is still in flight. Don't poll — if not ready, fail fast and the
     *     operator re-runs.
     *
     * Returns null when no refresh is needed.
     *
     * @param array<string, ?PullRequestSnapshot> $snapshots
     * @param list<RoleLabel>                     $mergedThisRun
     */
    private function refreshDocsBeforeMerge(
        PullRequestTarget $target,
        PullRequestSnapshot $current,
        array $snapshots,
        array $mergedThisRun,
    ): ?DocsRefreshResult {
        $conductorJustMerged = in_array(RoleLabel::Conductor, $mergedThisRun, true);
        $conductorPreviouslyMerged = ($snapshots[RoleLabel::Conductor->value] ?? null)?->isMerged() ?? false;
        if (!$conductorJustMerged && !$conductorPreviouslyMerged) {
            return null;
        }
        if ($conductorJustMerged) {
            $fresh = $this->awaitDownstreamUpdate($target, $current);
            if ($fresh instanceof PullRequestSnapshot && $fresh->headRefOid === $current->headRefOid) {
                $reason = sprintf(
                    'docs PR head SHA did not change after conductor merge (timed out waiting for downstream'
                    . ' re-render — head still %s)',
                    $current->headRefOid,
                );
                return DocsRefreshResult::blocked($fresh, $reason, [$reason]);
            }
        } else {
            $fresh = $this->api->findByHead($target->repo, $target->branch);
        }
        if (!$fresh instanceof PullRequestSnapshot) {
            $disappeared = 'docs PR disappeared before merge';
            return DocsRefreshResult::blocked(null, $disappeared, [$disappeared]);
        }
        $readiness = $this->api->getReadiness($target->repo, $fresh->number);
        if (!$readiness->isReady()) {
            $stopReason = $conductorJustMerged
                ? 'docs PR not ready after conductor downstream update'
                : 'docs PR not ready (re-checked after conductor was already merged)';
            return DocsRefreshResult::blocked($fresh, $stopReason, $readiness->blockingReasons);
        }
        return DocsRefreshResult::success($fresh, $readiness);
    }

    /**
     * Poll until the docs PR head SHA differs from the snapshot taken before
     * the conductor merge, or until the timeout elapses. Either way, return a
     * fresh snapshot — readiness is re-checked after this.
     */
    private function awaitDownstreamUpdate(
        PullRequestTarget $target,
        PullRequestSnapshot $before,
    ): ?PullRequestSnapshot {
        $deadline = $this->clock->now()->getTimestamp() + $this->downstreamTimeoutSeconds;
        $current = $before;
        while ($this->clock->now()->getTimestamp() < $deadline) {
            $current = $this->api->findByHead($target->repo, $target->branch);
            if (!$current instanceof PullRequestSnapshot) {
                return null;
            }
            if ($current->headRefOid !== $before->headRefOid) {
                return $current;
            }
            $this->clock->sleep(self::POLL_INTERVAL_SECONDS);
        }
        return $current;
    }

    /**
     * @param  list<PullRequestTarget> $targets
     * @return list<ShipReleaseStepResult>
     */
    private function markAllNotReached(array $targets): array
    {
        $out = [];
        foreach ($targets as $target) {
            $out[] = $this->notReachedStep($target, 'fatal precondition');
        }
        return $out;
    }

    private function retractApprovalStatus(string $repo, string $sha, string $reason): void
    {
        $prefix = 'ship-release failed: ';
        $room = self::STATUS_DESCRIPTION_MAX - mb_strlen($prefix);
        $description = $prefix . mb_substr($reason, 0, $room);
        try {
            $this->api->postCommitStatus(
                $repo,
                $sha,
                self::STATUS_CONTEXT,
                'failure',
                $description,
                $this->statusTargetUrl,
            );
        } catch (\RuntimeException) {
            // Best-effort. If the retraction itself fails, we still surface
            // the original failure via ShipReleaseStepResult.
        }
    }

    private function notReachedStep(PullRequestTarget $target, string $reason): ShipReleaseStepResult
    {
        return new ShipReleaseStepResult(
            $target,
            ShipReleaseStepStatus::NOT_REACHED,
            null,
            null,
            [$reason],
        );
    }

    /**
     * @param list<string> $reasons
     */
    private function blockedStep(PullRequestTarget $target, ?int $number, array $reasons): ShipReleaseStepResult
    {
        return new ShipReleaseStepResult(
            $target,
            ShipReleaseStepStatus::BLOCKED,
            $number,
            null,
            $reasons,
        );
    }
}
