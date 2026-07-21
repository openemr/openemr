<?php

/**
 * Merges the three release PRs (conductor → finalize → docs) in strict order.
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
 * Execution mode is operator-selected via Mode:
 *   - DryRun: preflight-only, no merges (probe every PR's readiness)
 *   - SemiAuto: merge Conductor only; Finalize + Docs left for manual review
 *   - FullAuto: merge all three; waits on the GitHub Release object before
 *     any downstream merge (Finalize + Docs) as a proxy for
 *     build-release-on-tag completing package assembly + upload. If
 *     packaging failed, ship-release blocks with the underlying error
 *     rather than propagating half-shipped state to dockers or the
 *     announcement pipeline.
 *
 * Approval gate is asymmetric: Conductor requires reviewDecision=APPROVED
 * (the one meaningful human review point in the pipeline); Docs and Finalize
 * are auto-generated bot content with no meaningful "human review" gate, so
 * their readiness checks skip the APPROVED requirement while still enforcing
 * not-draft + MERGEABLE + CLEAN + green status checks.
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
        private string $version,
        private int $downstreamTimeoutSeconds = 600,
        private Mode $mode = Mode::SemiAuto,
        private string $statusTargetUrl = '',
    ) {
    }

    /**
     * @param list<PullRequestTarget> $targets conductor, finalize, docs (any order — sorted internally)
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

        return match ($this->mode) {
            Mode::DryRun => new ShipReleaseResult($this->dryRunSteps($targets, $snapshots)),
            Mode::SemiAuto,
            Mode::FullAuto => new ShipReleaseResult(
                $this->executeMerges($targets, $snapshots, $preflight['readiness']),
            ),
        };
    }

    /**
     * Defensive sort + contract check. The 3-PR ship contract requires
     * exactly the Conductor, Docs, and Finalize targets in any order; this
     * method fails fast on a stale caller passing the wrong shape (e.g., an
     * extra target, a missing one, or duplicate mergeOrder values), since
     * the alternative is a silent half-failure deep in the downstream merge
     * logic.
     *
     * @param  list<PullRequestTarget> $targets
     * @return list<PullRequestTarget>
     */
    private function sortByMergeOrder(array $targets): array
    {
        if (count($targets) !== 3) {
            throw new \LogicException(
                'ship-release expects exactly 3 targets (Conductor + Finalize + Docs); got ' . count($targets),
            );
        }
        $roles = array_map(static fn (PullRequestTarget $t): string => $t->roleLabel->value, $targets);
        sort($roles);
        $expected = [RoleLabel::Conductor->value, RoleLabel::Finalize->value, RoleLabel::Docs->value];
        sort($expected);
        if ($roles !== $expected) {
            throw new \LogicException(
                'ship-release targets must be {Conductor, Finalize, Docs}; got {' . implode(', ', $roles) . '}',
            );
        }
        $orders = array_map(static fn (PullRequestTarget $t): int => $t->mergeOrder, $targets);
        if (count(array_unique($orders)) !== count($orders)) {
            throw new \LogicException('ship-release targets have duplicate mergeOrder values');
        }
        // Enforce Conductor-before-Finalize-before-Docs at the mergeOrder
        // level too — a role set with wrong mergeOrder ordering would pass
        // the role-set + dedup checks above but usort would then merge
        // out-of-sequence, violating the strict merge order. See
        // RoleLabel docblock for the rationale on why Docs is last.
        $conductor = $this->findRequired($targets, RoleLabel::Conductor);
        $finalize = $this->findRequired($targets, RoleLabel::Finalize);
        $docs = $this->findRequired($targets, RoleLabel::Docs);
        if ($conductor->mergeOrder >= $finalize->mergeOrder || $finalize->mergeOrder >= $docs->mergeOrder) {
            throw new \LogicException(
                'ship-release mergeOrder must put Conductor before Finalize before Docs; got Conductor='
                . $conductor->mergeOrder . ' Finalize=' . $finalize->mergeOrder
                . ' Docs=' . $docs->mergeOrder,
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
            $check = $this->api->getReadiness(
                $target->repo,
                $snapshot->number,
                $this->requiresApproval($target->roleLabel),
            );
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
     * target was ready at snapshot time.
     *
     * SemiAuto merges Conductor only; Finalize + Docs are marked
     * SKIPPED_BY_MODE (they were preflight-ready — a maintainer merges
     * them by hand after eyeballing the post-tag content).
     *
     * FullAuto proceeds through all three in order Conductor → Finalize
     * → Docs. Docs is last because merging it will trigger the future
     * auto-announce pipeline; putting Finalize (which triggers the docker
     * cascade via release-targets.yml) before Docs gives dockers a head
     * start. The orchestrator waits for the GitHub Release object to
     * exist before merging ANY downstream role (Finalize + Docs) — that
     * object is created by build-release-on-tag.yml once it finishes
     * package assembly + upload, so its presence is a proxy for
     * "packaging succeeded." If packaging failed, both downstream merges
     * block with the timeout reason; the operator debugs + reruns rather
     * than triggering dockers or announcements on top of a broken
     * release. Finalize + Docs each get a refresh pass (poll head SHA
     * + re-check readiness) before merging.
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

            // SemiAuto stops after Conductor — Finalize + Docs are left for
            // a maintainer to merge by hand after eyeballing the post-tag
            // content. Mark them SKIPPED_BY_MODE (not a failure — the
            // operator asked for exactly this shape).
            if (
                !$this->mode->mergesDownstream()
                && $target->roleLabel !== RoleLabel::Conductor
            ) {
                $steps[] = $this->skippedByModeStep($target, 'semi-auto: downstream PR left for manual merge');
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
            // Wait for the GitHub Release object to exist before merging
            // ANY downstream role (Finalize + Docs). Finalize would trigger
            // the docker cascade via release-targets.yml; no point
            // dockerizing on top of a broken release. Docs would trigger
            // the future auto-announce; no point announcing packages
            // whose download links 404. If packaging broke, ship-release
            // blocks with the underlying error and the operator debugs +
            // reruns rather than propagating half-shipped state downstream.
            // (The wait fires once per downstream role, but re-polls are
            // O(1) after the release exists — releaseExists() short-
            // circuits on the first successful poll.)
            if ($target->roleLabel !== RoleLabel::Conductor) {
                $releaseWait = $this->awaitReleaseObject($mergedThisRun);
                if ($releaseWait !== null) {
                    $steps[] = $this->blockedStep($target, $snapshot->number, [$releaseWait]);
                    $stopReason = $releaseWait;
                    continue;
                }
            }
            if ($target->roleLabel === RoleLabel::Docs || $target->roleLabel === RoleLabel::Finalize) {
                $refresh = $this->refreshDownstreamBeforeMerge($target, $snapshot, $snapshots, $mergedThisRun);
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
     * Between Conductor and Docs, wait for the openemr GitHub Release object
     * to exist. Only fires when Conductor was merged in *this* run — if the
     * Conductor was merged in a prior run, the release was either already
     * created or the operator is on manual recovery and we don't burn the
     * timeout waiting.
     *
     * Returns null when the release exists (or when no wait is needed);
     * returns a blocking-reason string when the wait timed out.
     *
     * @param list<RoleLabel> $mergedThisRun
     */
    private function awaitReleaseObject(array $mergedThisRun): ?string
    {
        if (!in_array(RoleLabel::Conductor, $mergedThisRun, true)) {
            return null;
        }
        $tag = 'v' . str_replace('.', '_', $this->version);
        $deadline = $this->clock->now()->getTimestamp() + $this->downstreamTimeoutSeconds;
        while ($this->clock->now()->getTimestamp() < $deadline) {
            if ($this->api->releaseExists('openemr/openemr', $tag)) {
                return null;
            }
            $this->clock->sleep(self::POLL_INTERVAL_SECONDS);
        }
        if ($this->api->releaseExists('openemr/openemr', $tag)) {
            return null;
        }
        return sprintf(
            'GitHub Release object %s not created after conductor merge (timed out waiting for'
            . ' build-release-on-tag)',
            $tag,
        );
    }

    /**
     * Two cases need a fresh state read before merging a downstream PR (Docs
     * or Finalize):
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
    private function refreshDownstreamBeforeMerge(
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
                    '%s PR head SHA did not change after conductor merge (timed out waiting for downstream'
                    . ' re-render — head still %s)',
                    $target->roleLabel->value,
                    $current->headRefOid,
                );
                return DocsRefreshResult::blocked($fresh, $reason, [$reason]);
            }
        } else {
            $fresh = $this->api->findByHead($target->repo, $target->branch);
        }
        if (!$fresh instanceof PullRequestSnapshot) {
            $disappeared = sprintf('%s PR disappeared before merge', $target->roleLabel->value);
            return DocsRefreshResult::blocked(null, $disappeared, [$disappeared]);
        }
        $readiness = $this->api->getReadiness(
            $target->repo,
            $fresh->number,
            $this->requiresApproval($target->roleLabel),
        );
        if (!$readiness->isReady()) {
            $stopReason = $conductorJustMerged
                ? sprintf('%s PR not ready after conductor downstream update', $target->roleLabel->value)
                : sprintf(
                    '%s PR not ready (re-checked after conductor was already merged)',
                    $target->roleLabel->value,
                );
            return DocsRefreshResult::blocked($fresh, $stopReason, $readiness->blockingReasons);
        }
        return DocsRefreshResult::success($fresh, $readiness);
    }

    /**
     * Poll until the downstream PR head SHA differs from the snapshot taken
     * before the conductor merge, or until the timeout elapses. Either way,
     * return a fresh snapshot — readiness is re-checked after this.
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
     * Conductor is the one meaningful human-review point in the pipeline;
     * Docs and Finalize are auto-generated bot content, so their readiness
     * checks skip the APPROVED requirement.
     */
    private function requiresApproval(RoleLabel $role): bool
    {
        return match ($role) {
            RoleLabel::Conductor => true,
            RoleLabel::Docs, RoleLabel::Finalize => false,
        };
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

    private function skippedByModeStep(PullRequestTarget $target, string $reason): ShipReleaseStepResult
    {
        return new ShipReleaseStepResult(
            $target,
            ShipReleaseStepStatus::SKIPPED_BY_MODE,
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
