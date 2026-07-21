<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\PullRequestReadiness;
use OpenEMR\Release\PullRequestSnapshot;
use OpenEMR\Release\PullRequestState;
use OpenEMR\Release\PullRequestTarget;
use OpenEMR\Release\RoleLabel;
use OpenEMR\Release\ShipReleaseOrchestrator;
use OpenEMR\Release\ShipReleaseStepResult;
use OpenEMR\Release\ShipReleaseStepStatus;
use OpenEMR\Tests\Isolated\Release\Fakes\FailingMergeApi;
use OpenEMR\Tests\Isolated\Release\Fakes\FakeClock;
use OpenEMR\Tests\Isolated\Release\Fakes\FakePullRequestApi;
use PHPUnit\Framework\TestCase;

final class ShipReleaseOrchestratorTest extends TestCase
{
    private const CONDUCTOR_REPO = 'openemr/openemr';
    private const CONDUCTOR_BRANCH = 'release-prep/rel-810';
    private const CONDUCTOR_BASE = 'rel-810';
    private const DOCS_REPO = 'openemr/website-openemr';
    private const DOCS_BRANCH = 'release-docs/8.1.0';

    /**
     * @return list<PullRequestTarget>
     */
    private function targets(): array
    {
        return PullRequestTarget::forRelease('8.1.0', 'rel-810');
    }

    private function ready(string $headSha): PullRequestReadiness
    {
        return new PullRequestReadiness($headSha, []);
    }

    private function open(int $number, string $head, string $base = 'master'): PullRequestSnapshot
    {
        return new PullRequestSnapshot($number, $head, $base, PullRequestState::Open);
    }

    private function merged(int $number, string $head, string $base = 'master'): PullRequestSnapshot
    {
        return new PullRequestSnapshot($number, $head, $base, PullRequestState::Merged);
    }

    private function closed(int $number, string $head, string $base = 'master'): PullRequestSnapshot
    {
        return new PullRequestSnapshot($number, $head, $base, PullRequestState::Closed);
    }

    private function openConductor(): PullRequestSnapshot
    {
        return $this->open(202, 'sha-conductor', self::CONDUCTOR_BASE);
    }

    private function mergedConductor(): PullRequestSnapshot
    {
        return $this->merged(202, 'sha-conductor', self::CONDUCTOR_BASE);
    }

    public function testHappyPathMergesBothInOrderAndPostsApprovalStatus(): void
    {
        $api = new FakePullRequestApi();
        $targets = $this->targets();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs-old'));
        // After conductor merge, the docs PR is re-rendered with a new head SHA.
        $api->setSnapshotAfterFinds(
            self::DOCS_REPO,
            self::DOCS_BRANCH,
            2,
            $this->open(303, 'sha-docs-new'),
        );
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs-new'));

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($targets);

        self::assertTrue($result->wasSuccessful());
        self::assertSame(
            [ShipReleaseStepStatus::MERGED, ShipReleaseStepStatus::MERGED],
            array_map(
                static fn (ShipReleaseStepResult $s): ShipReleaseStepStatus => $s->status,
                $result->steps,
            ),
        );
        self::assertSame(
            [['repo' => self::CONDUCTOR_REPO, 'number' => 202, 'expected' => 'sha-conductor'],
                ['repo' => self::DOCS_REPO, 'number' => 303, 'expected' => 'sha-docs-new']],
            $api->merges,
        );
        self::assertCount(2, $api->postedStatuses);
        self::assertSame(ShipReleaseOrchestrator::STATUS_CONTEXT, $api->postedStatuses[0]['context']);
        self::assertSame('sha-conductor', $api->postedStatuses[0]['sha']);
        self::assertSame('sha-docs-new', $api->postedStatuses[1]['sha']);
    }

    public function testConductorBlockedAtPreflightMergesNothing(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadiness(
            self::CONDUCTOR_REPO,
            202,
            new PullRequestReadiness('sha-conductor', ['check core-test conclusion=FAILURE']),
        );
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[0]->status);
        self::assertContains('check core-test conclusion=FAILURE', $result->steps[0]->reasons);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[1]->status);
        self::assertSame([], $api->merges);
        self::assertSame([], $api->postedStatuses);
    }

    public function testConductorReadyButDocsBlockedAtPreflightMergesNothing(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, new PullRequestReadiness(
            'sha-docs',
            ['reviewDecision=REVIEW_REQUIRED (need APPROVED)'],
        ));

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[1]->status);
        self::assertSame([], $api->merges);
    }

    public function testDocsFirstFatalRefusesToMergeAnything(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->merged(303, 'sha-docs'));

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertNotNull($result->fatalReason);
        self::assertStringContainsString('docs-first', $result->fatalReason);
        self::assertSame([], $api->merges);
        foreach ($result->steps as $step) {
            self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $step->status);
        }
    }

    public function testDryRunDoesNotMergeOrPostStatuses(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));

        $result = (new ShipReleaseOrchestrator($api, new FakeClock(), 600, true))->ship($this->targets());

        self::assertTrue($result->wasSuccessful());
        self::assertSame([], $api->merges);
        self::assertSame([], $api->postedStatuses);
        foreach ($result->steps as $step) {
            self::assertSame(ShipReleaseStepStatus::WOULD_MERGE, $step->status);
        }
    }

    public function testConductorAlreadyMergedRefetchesDocsBeforeMerging(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->mergedConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs-stale'));
        $api->setReadinessSequence(self::DOCS_REPO, 303, [
            $this->ready('sha-docs-stale'),
            $this->ready('sha-docs-fresh'),
        ]);
        $api->setSnapshotAfterFinds(
            self::DOCS_REPO,
            self::DOCS_BRANCH,
            2,
            $this->open(303, 'sha-docs-fresh'),
        );

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($this->targets());

        self::assertTrue($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::MERGED, $result->steps[1]->status);
        self::assertSame('sha-docs-fresh', $api->merges[0]['expected']);
    }

    public function testConductorAlreadyMergedBlocksDocsIfDownstreamStillInFlight(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->mergedConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadinessSequence(self::DOCS_REPO, 303, [
            $this->ready('sha-docs'),
            new PullRequestReadiness('sha-docs', ['check core-test status=IN_PROGRESS']),
        ]);

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[1]->status);
        self::assertContains('check core-test status=IN_PROGRESS', $result->steps[1]->reasons);
        self::assertSame([], $api->merges);
    }

    public function testDownstreamWaitTimeoutBlocksDocsMerge(): void
    {
        // Docs PR head SHA never changes during the wait — simulates the
        // conductor's downstream re-render workflow not firing in time.
        // Even if readiness still looks "clean", we must NOT merge stale
        // docs content (DRAFT→FINAL flip never happened).
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));

        $clock = new FakeClock();
        $result = (new ShipReleaseOrchestrator($api, $clock, 30))->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertGreaterThanOrEqual(30, $clock->totalSlept);
        self::assertSame(ShipReleaseStepStatus::MERGED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[1]->status);
        self::assertStringContainsString('did not change after conductor merge', $result->steps[1]->reasons[0]);
        // Only conductor merged; docs not.
        self::assertCount(1, $api->merges);
    }

    public function testWrongBaseBranchBlocksWithoutMerging(): void
    {
        // Conductor PR exists but has been opened against `master` instead of
        // the expected `rel-810`. Refuse to merge it (would ship the wrong content).
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->open(202, 'sha-conductor', 'master'));
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[0]->status);
        self::assertStringContainsString('PR base is master, expected rel-810', $result->steps[0]->reasons[0]);
        self::assertSame([], $api->merges);
    }

    public function testTargetsAreSortedByMergeOrderRegardlessOfInputOrder(): void
    {
        // Pass targets in reverse order; the orchestrator must still merge
        // conductor → docs.
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs-old'));
        $api->setSnapshotAfterFinds(
            self::DOCS_REPO,
            self::DOCS_BRANCH,
            2,
            $this->open(303, 'sha-docs-new'),
        );
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs-new'));

        $shuffled = $this->targets();
        $shuffled = [$shuffled[1], $shuffled[0]]; // docs, conductor

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($shuffled);

        self::assertTrue($result->wasSuccessful());
        self::assertSame(
            [self::CONDUCTOR_REPO, self::DOCS_REPO],
            array_column($api->merges, 'repo'),
        );
    }

    public function testMergeApiFailureReportsBlockedAndStopsSubsequentMerges(): void
    {
        // Simulate gh failing on the conductor merge (e.g. --match-head-commit
        // mismatch from a race). Conductor reports BLOCKED with the gh error,
        // docs is NOT_REACHED.
        $api = new FailingMergeApi('openemr/openemr', 202, 'gh: --match-head-commit does not match');
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[0]->status);
        self::assertStringContainsString('--match-head-commit does not match', $result->steps[0]->reasons[0]);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[1]->status);
    }

    public function testClosedWithoutMergingPrBlocks(): void
    {
        // A PR that was closed without merging (state=CLOSED, mergedAt=null)
        // must not be treated as "open and ready to merge".
        $api = new FakePullRequestApi();
        $api->setSnapshot(
            self::CONDUCTOR_REPO,
            self::CONDUCTOR_BRANCH,
            $this->closed(202, 'sha-conductor', self::CONDUCTOR_BASE),
        );
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));

        $result = (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[0]->status);
        self::assertStringContainsString('CLOSED without being merged', $result->steps[0]->reasons[0]);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[1]->status);
        self::assertSame([], $api->merges);
    }

    public function testDuplicateMergeOrderThrowsLogicException(): void
    {
        $api = new FakePullRequestApi();
        $targets = [
            new PullRequestTarget('a/x', 'b1', 'master', RoleLabel::Conductor, 1),
            new PullRequestTarget('a/y', 'b2', 'master', RoleLabel::Docs, 1),
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('duplicate mergeOrder');
        (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($targets);
    }

    public function testWrongTargetCountThrowsLogicException(): void
    {
        // Stale callers (e.g., still passing the pre-collapse 3-PR list,
        // or a single Conductor-only target) must fail fast rather than
        // silently half-shipping.
        $api = new FakePullRequestApi();
        $targets = [
            new PullRequestTarget('a/x', 'b1', 'master', RoleLabel::Conductor, 1),
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('exactly 2 targets');
        (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($targets);
    }

    public function testWrongTargetRoleSetThrowsLogicException(): void
    {
        // Two targets of correct count but wrong roles (e.g., two
        // Conductors, or a Conductor + something else) violates the
        // {Conductor, Docs} contract and must fail fast.
        $api = new FakePullRequestApi();
        $targets = [
            new PullRequestTarget('a/x', 'b1', 'master', RoleLabel::Conductor, 1),
            new PullRequestTarget('a/y', 'b2', 'master', RoleLabel::Conductor, 2),
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('{Conductor, Docs}');
        (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($targets);
    }

    public function testReversedMergeOrderThrowsLogicException(): void
    {
        // Correct role set {Conductor, Docs} with unique mergeOrder
        // values, but Docs.mergeOrder < Conductor.mergeOrder. The
        // role-set + dedup checks would pass, but usort would then put
        // Docs first — silently violating the strict conductor → docs
        // merge sequence. Must fail fast.
        $api = new FakePullRequestApi();
        $targets = [
            new PullRequestTarget('a/x', 'b1', 'master', RoleLabel::Conductor, 2),
            new PullRequestTarget('a/y', 'b2', 'master', RoleLabel::Docs, 1),
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conductor before Docs');
        (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($targets);
    }

    public function testMergeFailureRetractsApprovalStatus(): void
    {
        // Verify the success status is followed by a failure status on the
        // same head SHA so a stale release/ship-approved=success doesn't
        // remain on the PR for branch protection to honor.
        $api = new FailingMergeApi('openemr/openemr', 202, 'gh: api error');
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));

        (new ShipReleaseOrchestrator($api, new FakeClock()))->ship($this->targets());

        // Statuses on conductor head: success (pre-merge) then failure (retraction).
        $conductorStatuses = array_values(array_filter(
            $api->postedStatuses,
            static fn (array $s): bool => $s['sha'] === 'sha-conductor',
        ));
        self::assertCount(2, $conductorStatuses);
        self::assertSame('success', $conductorStatuses[0]['state']);
        self::assertSame('failure', $conductorStatuses[1]['state']);
    }
}
