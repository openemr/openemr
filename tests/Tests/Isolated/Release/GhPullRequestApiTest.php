<?php

/**
 * Unit tests for the pure helpers on GhPullRequestApi. The shell-touching
 * methods are exercised end-to-end through the orchestrator with a fake API.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\GhPullRequestApi;
use OpenEMR\Release\ShipReleaseOrchestrator;
use PHPUnit\Framework\TestCase;

final class GhPullRequestApiTest extends TestCase
{
    public function testRollupSkipsOwnContextEvenWhenItIsFailure(): void
    {
        // Regression: a prior ship-release run that failed left
        // release/ship-approved=failure on the head SHA. The next preflight
        // must not treat that as a blocking external check.
        $rollup = [
            ['context' => 'ci/build', 'state' => 'SUCCESS'],
            ['context' => ShipReleaseOrchestrator::STATUS_CONTEXT, 'state' => 'FAILURE'],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertSame([], $reasons);
    }

    public function testRollupBlocksOnFailingExternalCheck(): void
    {
        $rollup = [
            ['context' => 'ci/build', 'state' => 'FAILURE'],
            ['context' => ShipReleaseOrchestrator::STATUS_CONTEXT, 'state' => 'SUCCESS'],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertCount(1, $reasons);
        self::assertStringContainsString('ci/build', $reasons[0]);
        self::assertStringContainsString('FAILURE', $reasons[0]);
    }

    public function testRollupBlocksOnPendingCheckRun(): void
    {
        $rollup = [
            ['name' => 'phpstan', 'status' => 'IN_PROGRESS', 'conclusion' => null],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertCount(1, $reasons);
        self::assertStringContainsString('phpstan', $reasons[0]);
        self::assertStringContainsString('IN_PROGRESS', $reasons[0]);
    }

    public function testRollupAllowsNeutralAndSkippedConclusions(): void
    {
        $rollup = [
            ['name' => 'optional-job', 'status' => 'COMPLETED', 'conclusion' => 'NEUTRAL'],
            ['name' => 'skipped-job', 'status' => 'COMPLETED', 'conclusion' => 'SKIPPED'],
            ['name' => 'green-job', 'status' => 'COMPLETED', 'conclusion' => 'SUCCESS'],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertSame([], $reasons);
    }

    public function testRollupBlocksOnLegacyExpectedState(): void
    {
        $rollup = [
            ['context' => 'required/check', 'state' => 'EXPECTED'],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertCount(1, $reasons);
        self::assertStringContainsString('EXPECTED', $reasons[0]);
    }

    public function testRollupSkipsIgnoredCheckByName(): void
    {
        $rollup = [
            ['name' => 'PHP 8.6 - Isolated Tests', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE'],
            ['name' => 'green-job', 'status' => 'COMPLETED', 'conclusion' => 'SUCCESS'],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup(
            $rollup,
            ShipReleaseOrchestrator::STATUS_CONTEXT,
            ['PHP 8.6 - Isolated Tests'],
        );

        self::assertSame([], $reasons);
    }

    public function testRollupSkipsIgnoredLegacyStatusByContext(): void
    {
        $rollup = [
            ['context' => 'ci/upstream-flake', 'state' => 'FAILURE'],
            ['context' => 'ci/build', 'state' => 'SUCCESS'],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup(
            $rollup,
            ShipReleaseOrchestrator::STATUS_CONTEXT,
            ['ci/upstream-flake'],
        );

        self::assertSame([], $reasons);
    }

    public function testRollupStillBlocksOnCheckNotInIgnoreList(): void
    {
        $rollup = [
            ['name' => 'PHP 8.6 - Isolated Tests', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE'],
            ['name' => 'phpstan', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE'],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup(
            $rollup,
            ShipReleaseOrchestrator::STATUS_CONTEXT,
            ['PHP 8.6 - Isolated Tests'],
        );

        self::assertCount(1, $reasons);
        self::assertStringContainsString('phpstan', $reasons[0]);
    }

    /**
     * @param  list<array<string, mixed>>                                            $statusCheckRollup
     * @param  list<array{state: string, author?: array{login?: string}}>            $latestReviews
     * @return array{
     *     isDraft: bool,
     *     mergeable: string,
     *     mergeStateStatus: string,
     *     reviewDecision: ?string,
     *     statusCheckRollup: list<array<string, mixed>>,
     *     latestReviews: list<array{state: string, author?: array{login?: string}}>,
     * }
     */
    private static function prData(
        bool $isDraft = false,
        string $mergeable = 'MERGEABLE',
        string $mergeStateStatus = 'CLEAN',
        ?string $reviewDecision = 'APPROVED',
        array $statusCheckRollup = [],
        array $latestReviews = [],
    ): array {
        return [
            'isDraft' => $isDraft,
            'mergeable' => $mergeable,
            'mergeStateStatus' => $mergeStateStatus,
            'reviewDecision' => $reviewDecision,
            'statusCheckRollup' => $statusCheckRollup,
            'latestReviews' => $latestReviews,
        ];
    }

    public function testReadinessAcceptsCleanMergeStateWithNoBlockers(): void
    {
        $reasons = GhPullRequestApi::reasonsFromPullRequestData(self::prData(), true, []);

        self::assertSame([], $reasons);
    }

    public function testReadinessAcceptsUnstableWhenIgnoreListClearsRollup(): void
    {
        // Regression: pre-#13570-followup ship-release preflight rejected any
        // mergeStateStatus != CLEAN, so UNSTABLE (= non-required check failing)
        // caused by an ignored check still blocked. Now UNSTABLE passes when
        // the rollup evaluation returned no blockers.
        $data = self::prData(
            mergeStateStatus: 'UNSTABLE',
            statusCheckRollup: [
                ['name' => 'PHP 8.6 - Isolated Tests', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE'],
                ['name' => 'phpstan', 'status' => 'COMPLETED', 'conclusion' => 'SUCCESS'],
            ],
        );

        $reasons = GhPullRequestApi::reasonsFromPullRequestData(
            $data,
            true,
            ['PHP 8.6 - Isolated Tests'],
        );

        self::assertSame([], $reasons);
    }

    public function testReadinessBlocksUnstableWhenRollupHasUnignoredFailure(): void
    {
        // UNSTABLE is only accepted when the ignore-list fully clears the
        // rollup. A failing check outside the ignore-list keeps mergeStateStatus
        // as a blocker AND surfaces the check itself.
        $data = self::prData(
            mergeStateStatus: 'UNSTABLE',
            statusCheckRollup: [
                ['name' => 'PHP 8.6 - Isolated Tests', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE'],
                ['name' => 'phpstan', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE'],
            ],
        );

        $reasons = GhPullRequestApi::reasonsFromPullRequestData(
            $data,
            true,
            ['PHP 8.6 - Isolated Tests'],
        );

        self::assertContains('mergeStateStatus=UNSTABLE (need CLEAN)', $reasons);
        self::assertTrue(
            (bool) array_filter($reasons, static fn (string $r): bool => str_contains($r, 'phpstan')),
            'phpstan failure must still surface as a blocking reason',
        );
    }

    public function testReadinessBlocksBlockedMergeStateEvenWithCleanRollup(): void
    {
        // BLOCKED means a REQUIRED check failed (or review missing) —
        // fundamentally different from UNSTABLE and never ignorable via the
        // check-name list. Preserve the hard block.
        $data = self::prData(
            mergeStateStatus: 'BLOCKED',
            statusCheckRollup: [
                ['name' => 'phpstan', 'status' => 'COMPLETED', 'conclusion' => 'SUCCESS'],
            ],
        );

        $reasons = GhPullRequestApi::reasonsFromPullRequestData($data, true, []);

        self::assertContains('mergeStateStatus=BLOCKED (need CLEAN)', $reasons);
    }

    public function testReadinessBlocksDirtyMergeStateEvenWhenIgnoreListIsPresent(): void
    {
        // DIRTY means merge conflicts — not something an operator-supplied
        // check ignore-list can plausibly clear. Any non-CLEAN + non-UNSTABLE
        // state stays blocking.
        $data = self::prData(
            mergeStateStatus: 'DIRTY',
            statusCheckRollup: [
                ['name' => 'PHP 8.6 - Isolated Tests', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE'],
            ],
        );

        $reasons = GhPullRequestApi::reasonsFromPullRequestData(
            $data,
            true,
            ['PHP 8.6 - Isolated Tests'],
        );

        self::assertContains('mergeStateStatus=DIRTY (need CLEAN)', $reasons);
    }

    public function testReadinessBlocksDraftPr(): void
    {
        $data = self::prData(isDraft: true, mergeStateStatus: 'BLOCKED');

        $reasons = GhPullRequestApi::reasonsFromPullRequestData($data, true, []);

        self::assertContains('PR is a draft', $reasons);
    }

    public function testReadinessSkipsApprovalCheckWhenNotRequired(): void
    {
        $data = self::prData(reviewDecision: null);

        $reasons = GhPullRequestApi::reasonsFromPullRequestData($data, false, []);

        self::assertSame([], $reasons);
    }

    public function testReadinessBlocksMissingApprovalWhenRequired(): void
    {
        $data = self::prData(reviewDecision: null);

        $reasons = GhPullRequestApi::reasonsFromPullRequestData($data, true, []);

        self::assertContains('reviewDecision=null (need APPROVED)', $reasons);
    }

    public function testReadinessFlagsChangesRequestedReview(): void
    {
        $data = self::prData(latestReviews: [
            ['state' => 'CHANGES_REQUESTED', 'author' => ['login' => 'gatekeeper']],
            ['state' => 'APPROVED', 'author' => ['login' => 'other']],
        ]);

        $reasons = GhPullRequestApi::reasonsFromPullRequestData($data, true, []);

        self::assertContains('CHANGES_REQUESTED review by gatekeeper', $reasons);
    }

    public function testReadinessToleratesDraftWhenRequireNonDraftIsFalse(): void
    {
        // Docs + Finalize PRs are auto-drafted by their generator workflows
        // and auto-flipped by post-tag workflows. Blocking downstream targets
        // on draft state at preflight would deadlock the ship. The orchestrator
        // passes requireNonDraft=false for those roles.
        $data = self::prData(isDraft: true);

        $reasons = GhPullRequestApi::reasonsFromPullRequestData($data, false, [], false);

        self::assertSame([], $reasons);
    }

    public function testRollupDedupesStaleFailureWhenLatestSuccessExists(): void
    {
        // Regression: rerunning a workflow leaves the older attempt in the
        // rollup alongside the newer one. Ship-release must only consider the
        // latest per check name; otherwise a stale FAILURE from before a fix
        // landed keeps blocking preflight even after a green re-run.
        $rollup = [
            [
                'name' => 'validate',
                'status' => 'COMPLETED',
                'conclusion' => 'FAILURE',
                'completedAt' => '2026-08-16T18:56:00Z',
            ],
            [
                'name' => 'validate',
                'status' => 'COMPLETED',
                'conclusion' => 'SUCCESS',
                'completedAt' => '2026-08-16T20:14:19Z',
            ],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertSame([], $reasons);
    }

    public function testRollupDedupeKeepsLatestFailureOverStaleSuccess(): void
    {
        // Symmetric: if the newest run is FAILURE and an older SUCCESS exists,
        // the FAILURE must still block. Prevents "hide bug by re-running until
        // green" logic errors and confirms dedupe is timestamp-based, not
        // biased toward SUCCESS.
        $rollup = [
            [
                'name' => 'phpstan',
                'status' => 'COMPLETED',
                'conclusion' => 'SUCCESS',
                'completedAt' => '2026-08-16T18:00:00Z',
            ],
            [
                'name' => 'phpstan',
                'status' => 'COMPLETED',
                'conclusion' => 'FAILURE',
                'completedAt' => '2026-08-16T19:00:00Z',
            ],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertCount(1, $reasons);
        self::assertStringContainsString('phpstan', $reasons[0]);
        self::assertStringContainsString('FAILURE', $reasons[0]);
    }

    public function testRollupDedupeUsesStartedAtWhenCompletedAtMissing(): void
    {
        // In-progress reruns have startedAt but no completedAt. A new
        // IN_PROGRESS re-run of an old FAILURE should win the dedupe (it's
        // the more recent attempt) and block preflight as pending, so the
        // operator waits for it to complete rather than acting on a stale
        // result.
        $rollup = [
            [
                'name' => 'validate',
                'status' => 'COMPLETED',
                'conclusion' => 'FAILURE',
                'completedAt' => '2026-08-16T18:56:00Z',
                'startedAt' => '2026-08-16T18:55:00Z',
            ],
            [
                'name' => 'validate',
                'status' => 'IN_PROGRESS',
                'conclusion' => null,
                'startedAt' => '2026-08-16T20:14:00Z',
            ],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertCount(1, $reasons);
        self::assertStringContainsString('IN_PROGRESS', $reasons[0]);
    }

    public function testRollupDedupePreservesFirstSeenOrderWhenReplacingNewerAttempt(): void
    {
        // Regression: replacing a check with a newer attempt must NOT
        // overwrite its first-seen sequence position. Order in the rollup:
        // check A (seq 1), check B (seq 2), check A newer (seq 3, replaces).
        // Dedupe should emit A before B — A was first seen at seq 1, even
        // though the winning entry is a later re-run.
        $rollup = [
            ['name' => 'A', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE',
                'completedAt' => '2026-08-16T18:00:00Z'],
            ['name' => 'B', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE',
                'completedAt' => '2026-08-16T18:30:00Z'],
            ['name' => 'A', 'status' => 'COMPLETED', 'conclusion' => 'FAILURE',
                'completedAt' => '2026-08-16T19:00:00Z'],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertCount(2, $reasons);
        self::assertStringContainsString('A', $reasons[0]);
        self::assertStringContainsString('B', $reasons[1]);
    }

    public function testRollupDedupeAppliesToLegacyStatusesByContext(): void
    {
        // Legacy commit statuses re-post to the same context. Only the latest
        // by createdAt should be considered.
        $rollup = [
            [
                'context' => 'codecov/project',
                'state' => 'FAILURE',
                'createdAt' => '2026-08-16T18:00:00Z',
            ],
            [
                'context' => 'codecov/project',
                'state' => 'SUCCESS',
                'createdAt' => '2026-08-16T20:00:00Z',
            ],
        ];

        $reasons = GhPullRequestApi::reasonsFromStatusRollup($rollup, ShipReleaseOrchestrator::STATUS_CONTEXT);

        self::assertSame([], $reasons);
    }
}
