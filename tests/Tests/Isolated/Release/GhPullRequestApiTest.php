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
}
