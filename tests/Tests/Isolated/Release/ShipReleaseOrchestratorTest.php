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

use OpenEMR\Release\Mode;
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
    private const VERSION = '8.1.0';
    private const REL_BRANCH = 'rel-810';
    private const RELEASE_TAG = 'v8_1_0';
    private const OPENEMR_REPO = 'openemr/openemr';
    private const CONDUCTOR_REPO = self::OPENEMR_REPO;
    private const CONDUCTOR_BRANCH = 'release-prep/rel-810';
    private const CONDUCTOR_BASE = 'rel-810';
    private const DOCS_REPO = 'openemr/website-openemr';
    private const DOCS_BRANCH = 'release-docs/8.1.0';
    private const FINALIZE_REPO = self::OPENEMR_REPO;
    private const FINALIZE_BRANCH = 'release-finalize/rel-810';

    /**
     * @return list<PullRequestTarget>
     */
    private function targets(): array
    {
        return PullRequestTarget::forRelease(self::VERSION, self::REL_BRANCH);
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

    /**
     * Install a ready open Finalize PR so tests that only care about
     * Conductor + Docs behavior don't have to spell out the third target
     * every time. Snapshot head is 'sha-finalize' unless overridden.
     */
    private function primeFinalize(FakePullRequestApi $api, string $head = 'sha-finalize'): void
    {
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, $head));
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready($head));
    }

    /**
     * FullAuto orchestrator wired to a happy release-exists check by default.
     * Individual tests can override releaseExists() before shipping.
     */
    private function fullAuto(
        FakePullRequestApi $api,
        FakeClock $clock,
        int $timeoutSeconds = 600,
    ): ShipReleaseOrchestrator {
        $api->setReleaseExists(self::OPENEMR_REPO, self::RELEASE_TAG, true);
        return new ShipReleaseOrchestrator(
            $api,
            $clock,
            self::VERSION,
            $timeoutSeconds,
            Mode::FullAuto,
        );
    }

    private function semiAuto(FakePullRequestApi $api, FakeClock $clock): ShipReleaseOrchestrator
    {
        return new ShipReleaseOrchestrator(
            $api,
            $clock,
            self::VERSION,
            600,
            Mode::SemiAuto,
        );
    }

    public function testHappyPathFullAutoMergesAllThreeInOrderAndPostsApprovalStatus(): void
    {
        $api = new FakePullRequestApi();
        $targets = $this->targets();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs-old'));
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize-old'));
        // After conductor merge, the docs PR is re-rendered with a new head SHA.
        // preflight = 1 find, then executeMerges: 1 refresh + poll finds until swap
        $api->setSnapshotAfterFinds(
            self::DOCS_REPO,
            self::DOCS_BRANCH,
            2,
            $this->open(303, 'sha-docs-new'),
        );
        // Finalize PR gets its post-tag content update after the finalize job
        // in release-prep.yml re-renders it.
        $api->setSnapshotAfterFinds(
            self::FINALIZE_REPO,
            self::FINALIZE_BRANCH,
            2,
            $this->open(404, 'sha-finalize-new'),
        );
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs-new'));
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize-new'));

        $result = $this->fullAuto($api, new FakeClock())->ship($targets);

        self::assertTrue($result->wasSuccessful());
        self::assertSame(
            [ShipReleaseStepStatus::MERGED, ShipReleaseStepStatus::MERGED, ShipReleaseStepStatus::MERGED],
            array_map(
                static fn (ShipReleaseStepResult $s): ShipReleaseStepStatus => $s->status,
                $result->steps,
            ),
        );
        self::assertSame(
            [
                ['repo' => self::CONDUCTOR_REPO, 'number' => 202, 'expected' => 'sha-conductor'],
                ['repo' => self::DOCS_REPO, 'number' => 303, 'expected' => 'sha-docs-new'],
                ['repo' => self::FINALIZE_REPO, 'number' => 404, 'expected' => 'sha-finalize-new'],
            ],
            $api->merges,
        );
        self::assertCount(3, $api->postedStatuses);
        self::assertSame(ShipReleaseOrchestrator::STATUS_CONTEXT, $api->postedStatuses[0]['context']);
        self::assertSame('sha-conductor', $api->postedStatuses[0]['sha']);
        self::assertSame('sha-docs-new', $api->postedStatuses[1]['sha']);
        self::assertSame('sha-finalize-new', $api->postedStatuses[2]['sha']);
    }

    public function testSemiAutoMergesConductorOnly(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize'));

        $result = $this->semiAuto($api, new FakeClock())->ship($this->targets());

        // Conductor merged, Docs + Finalize SKIPPED_BY_MODE (maintainer merges
        // them). SKIPPED_BY_MODE is a success step — the operator asked for
        // exactly this shape, so the overall run exits 0.
        self::assertTrue($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::MERGED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::SKIPPED_BY_MODE, $result->steps[1]->status);
        self::assertSame(ShipReleaseStepStatus::SKIPPED_BY_MODE, $result->steps[2]->status);
        self::assertSame(
            [['repo' => self::CONDUCTOR_REPO, 'number' => 202, 'expected' => 'sha-conductor']],
            $api->merges,
        );
        // No wait for the release object in semi-auto — conductor merges then stops.
        self::assertSame(0, $api->getReleaseExistsCallCount(self::OPENEMR_REPO, self::RELEASE_TAG));
        self::assertStringContainsString('semi-auto', $result->steps[1]->reasons[0]);
        self::assertStringContainsString('semi-auto', $result->steps[2]->reasons[0]);
    }

    public function testFullAutoWaitsForReleaseObjectBeforeMergingDocs(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs-old'));
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize-old'));
        $api->setSnapshotAfterFinds(self::DOCS_REPO, self::DOCS_BRANCH, 2, $this->open(303, 'sha-docs-new'));
        $api->setSnapshotAfterFinds(
            self::FINALIZE_REPO,
            self::FINALIZE_BRANCH,
            2,
            $this->open(404, 'sha-finalize-new'),
        );
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs-new'));
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize-new'));
        // Release object appears after 3 polls (build-release-on-tag finishing).
        $api->setReleaseExistsAfterCalls(self::OPENEMR_REPO, self::RELEASE_TAG, 3);

        $clock = new FakeClock();
        $orchestrator = new ShipReleaseOrchestrator(
            $api,
            $clock,
            self::VERSION,
            600,
            Mode::FullAuto,
        );

        $result = $orchestrator->ship($this->targets());

        self::assertTrue($result->wasSuccessful());
        // All three merged.
        self::assertCount(3, $api->merges);
        // Polled >= 3 times before proceeding to docs merge.
        self::assertGreaterThanOrEqual(
            3,
            $api->getReleaseExistsCallCount(self::OPENEMR_REPO, self::RELEASE_TAG),
        );
        self::assertGreaterThan(0, $clock->totalSlept);
    }

    public function testFullAutoBlocksDocsWhenReleaseObjectNeverCreated(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize'));
        // Release never appears — the release-existence flag stays false.
        $api->setReleaseExists(self::OPENEMR_REPO, self::RELEASE_TAG, false);

        $clock = new FakeClock();
        $orchestrator = new ShipReleaseOrchestrator(
            $api,
            $clock,
            self::VERSION,
            30,
            Mode::FullAuto,
        );

        $result = $orchestrator->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::MERGED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[1]->status);
        self::assertStringContainsString('GitHub Release object', $result->steps[1]->reasons[0]);
        self::assertStringContainsString('build-release-on-tag', $result->steps[1]->reasons[0]);
        // Finalize is downstream of Docs — must be NOT_REACHED, not merged.
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[2]->status);
        // Only conductor merged.
        self::assertCount(1, $api->merges);
        self::assertGreaterThanOrEqual(30, $clock->totalSlept);
    }

    public function testFinalizeRefreshedAndMergedAfterDocs(): void
    {
        // Finalize PR gets an updated head SHA after the docs merge (release-
        // prep.yml's finalize job re-renders it with post-tag content).
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs-old'));
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize-old'));
        $api->setSnapshotAfterFinds(self::DOCS_REPO, self::DOCS_BRANCH, 2, $this->open(303, 'sha-docs-new'));
        $api->setSnapshotAfterFinds(
            self::FINALIZE_REPO,
            self::FINALIZE_BRANCH,
            2,
            $this->open(404, 'sha-finalize-new'),
        );
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs-new'));
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize-new'));

        $result = $this->fullAuto($api, new FakeClock())->ship($this->targets());

        self::assertTrue($result->wasSuccessful());
        // Finalize merge should use the *new* head SHA — proof the refresh
        // + re-check ran before merge.
        self::assertSame('sha-finalize-new', $api->merges[2]['expected']);
    }

    public function testApprovalGateAsymmetricConductorRequiresApprovalDocsAndFinalizeDoNot(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize'));

        $this->semiAuto($api, new FakeClock())->ship($this->targets());

        $byRole = [];
        foreach ($api->readinessCalls as $call) {
            $byRole[$call['repo'] . '#' . $call['number']] = $call['requireApproval'];
        }
        self::assertTrue($byRole[self::CONDUCTOR_REPO . '#202']);
        self::assertFalse($byRole[self::DOCS_REPO . '#303']);
        self::assertFalse($byRole[self::FINALIZE_REPO . '#404']);
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
        $this->primeFinalize($api);

        $result = $this->semiAuto($api, new FakeClock())->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[0]->status);
        self::assertContains('check core-test conclusion=FAILURE', $result->steps[0]->reasons);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[1]->status);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[2]->status);
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
            ['mergeStateStatus=BLOCKED (need CLEAN)'],
        ));
        $this->primeFinalize($api);

        $result = $this->semiAuto($api, new FakeClock())->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[1]->status);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[2]->status);
        self::assertSame([], $api->merges);
    }

    public function testDocsFirstFatalRefusesToMergeAnything(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->merged(303, 'sha-docs'));
        $this->primeFinalize($api);

        $result = $this->semiAuto($api, new FakeClock())->ship($this->targets());

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
        $this->primeFinalize($api);

        $orchestrator = new ShipReleaseOrchestrator(
            $api,
            new FakeClock(),
            self::VERSION,
            600,
            Mode::DryRun,
        );
        $result = $orchestrator->ship($this->targets());

        self::assertTrue($result->wasSuccessful());
        self::assertSame([], $api->merges);
        self::assertSame([], $api->postedStatuses);
        self::assertCount(3, $result->steps);
        foreach ($result->steps as $step) {
            self::assertSame(ShipReleaseStepStatus::WOULD_MERGE, $step->status);
        }
    }

    public function testConductorAlreadyMergedRefetchesDocsBeforeMerging(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->mergedConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs-stale'));
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize'));
        $api->setReadinessSequence(self::DOCS_REPO, 303, [
            $this->ready('sha-docs-stale'),
            $this->ready('sha-docs-fresh'),
        ]);
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize'));
        $api->setSnapshotAfterFinds(
            self::DOCS_REPO,
            self::DOCS_BRANCH,
            2,
            $this->open(303, 'sha-docs-fresh'),
        );

        // Recovery run: conductor was merged in an earlier run, so the release
        // object should already exist (build-release-on-tag ran during that
        // run). No wait needed in the current run.
        $result = $this->fullAuto($api, new FakeClock())->ship($this->targets());

        self::assertTrue($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::MERGED, $result->steps[1]->status);
        self::assertSame(ShipReleaseStepStatus::MERGED, $result->steps[2]->status);
        self::assertSame('sha-docs-fresh', $api->merges[0]['expected']);
    }

    public function testConductorAlreadyMergedBlocksDocsIfDownstreamStillInFlight(): void
    {
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->mergedConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize'));
        $api->setReadinessSequence(self::DOCS_REPO, 303, [
            $this->ready('sha-docs'),
            new PullRequestReadiness('sha-docs', ['check core-test status=IN_PROGRESS']),
        ]);
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize'));

        $result = $this->fullAuto($api, new FakeClock())->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[1]->status);
        self::assertContains('check core-test status=IN_PROGRESS', $result->steps[1]->reasons);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[2]->status);
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
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize'));

        $clock = new FakeClock();
        $result = $this->fullAuto($api, $clock, 30)->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertGreaterThanOrEqual(30, $clock->totalSlept);
        self::assertSame(ShipReleaseStepStatus::MERGED, $result->steps[0]->status);
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[1]->status);
        self::assertStringContainsString('did not change after conductor merge', $result->steps[1]->reasons[0]);
        // Finalize downstream of docs — must be NOT_REACHED once docs is blocked.
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[2]->status);
        // Only conductor merged; docs + finalize not.
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
        $this->primeFinalize($api);

        $result = $this->semiAuto($api, new FakeClock())->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[0]->status);
        self::assertStringContainsString('PR base is master, expected rel-810', $result->steps[0]->reasons[0]);
        self::assertSame([], $api->merges);
    }

    public function testTargetsAreSortedByMergeOrderRegardlessOfInputOrder(): void
    {
        // Pass targets in reverse order; the orchestrator must still merge
        // conductor → docs → finalize.
        $api = new FakePullRequestApi();
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs-old'));
        $api->setSnapshot(self::FINALIZE_REPO, self::FINALIZE_BRANCH, $this->open(404, 'sha-finalize-old'));
        $api->setSnapshotAfterFinds(
            self::DOCS_REPO,
            self::DOCS_BRANCH,
            2,
            $this->open(303, 'sha-docs-new'),
        );
        $api->setSnapshotAfterFinds(
            self::FINALIZE_REPO,
            self::FINALIZE_BRANCH,
            2,
            $this->open(404, 'sha-finalize-new'),
        );
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs-new'));
        $api->setReadiness(self::FINALIZE_REPO, 404, $this->ready('sha-finalize-new'));

        $shuffled = $this->targets();
        // Reverse: finalize, docs, conductor.
        $shuffled = [$shuffled[2], $shuffled[1], $shuffled[0]];

        $result = $this->fullAuto($api, new FakeClock())->ship($shuffled);

        self::assertTrue($result->wasSuccessful());
        self::assertSame(
            [self::CONDUCTOR_REPO, self::DOCS_REPO, self::FINALIZE_REPO],
            array_column($api->merges, 'repo'),
        );
    }

    public function testMergeApiFailureReportsBlockedAndStopsSubsequentMerges(): void
    {
        // Simulate gh failing on the conductor merge (e.g. --match-head-commit
        // mismatch from a race). Conductor reports BLOCKED with the gh error,
        // docs + finalize are NOT_REACHED.
        $api = new FailingMergeApi('openemr/openemr', 202, 'gh: --match-head-commit does not match');
        $api->setSnapshot(self::CONDUCTOR_REPO, self::CONDUCTOR_BRANCH, $this->openConductor());
        $api->setSnapshot(self::DOCS_REPO, self::DOCS_BRANCH, $this->open(303, 'sha-docs'));
        $api->setReadiness(self::CONDUCTOR_REPO, 202, $this->ready('sha-conductor'));
        $api->setReadiness(self::DOCS_REPO, 303, $this->ready('sha-docs'));
        $this->primeFinalize($api);

        $result = $this->semiAuto($api, new FakeClock())->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[0]->status);
        self::assertStringContainsString('--match-head-commit does not match', $result->steps[0]->reasons[0]);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[1]->status);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[2]->status);
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
        $this->primeFinalize($api);

        $result = $this->semiAuto($api, new FakeClock())->ship($this->targets());

        self::assertFalse($result->wasSuccessful());
        self::assertSame(ShipReleaseStepStatus::BLOCKED, $result->steps[0]->status);
        self::assertStringContainsString('CLOSED without being merged', $result->steps[0]->reasons[0]);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[1]->status);
        self::assertSame(ShipReleaseStepStatus::NOT_REACHED, $result->steps[2]->status);
        self::assertSame([], $api->merges);
    }

    public function testDuplicateMergeOrderThrowsLogicException(): void
    {
        $api = new FakePullRequestApi();
        $targets = [
            new PullRequestTarget('a/x', 'b1', 'master', RoleLabel::Conductor, 1),
            new PullRequestTarget('a/y', 'b2', 'master', RoleLabel::Docs, 1),
            new PullRequestTarget('a/z', 'b3', 'master', RoleLabel::Finalize, 1),
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('duplicate mergeOrder');
        $this->semiAuto($api, new FakeClock())->ship($targets);
    }

    public function testWrongTargetCountThrowsLogicException(): void
    {
        // Stale callers (e.g., still passing the pre-3b 2-PR list, or a
        // single Conductor-only target) must fail fast rather than silently
        // half-shipping.
        $api = new FakePullRequestApi();
        $targets = [
            new PullRequestTarget('a/x', 'b1', 'master', RoleLabel::Conductor, 1),
            new PullRequestTarget('a/y', 'b2', 'master', RoleLabel::Docs, 2),
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('exactly 3 targets');
        $this->semiAuto($api, new FakeClock())->ship($targets);
    }

    public function testWrongTargetRoleSetThrowsLogicException(): void
    {
        // Three targets of correct count but wrong roles (e.g., two
        // Conductors) violates the {Conductor, Docs, Finalize} contract
        // and must fail fast.
        $api = new FakePullRequestApi();
        $targets = [
            new PullRequestTarget('a/x', 'b1', 'master', RoleLabel::Conductor, 1),
            new PullRequestTarget('a/y', 'b2', 'master', RoleLabel::Conductor, 2),
            new PullRequestTarget('a/z', 'b3', 'master', RoleLabel::Finalize, 3),
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('{Conductor, Docs, Finalize}');
        $this->semiAuto($api, new FakeClock())->ship($targets);
    }

    public function testReversedMergeOrderThrowsLogicException(): void
    {
        // Correct role set {Conductor, Docs, Finalize} with unique
        // mergeOrder values, but Docs.mergeOrder < Conductor.mergeOrder.
        // The role-set + dedup checks would pass, but usort would then put
        // Docs first — silently violating the strict conductor → docs →
        // finalize merge sequence. Must fail fast.
        $api = new FakePullRequestApi();
        $targets = [
            new PullRequestTarget('a/x', 'b1', 'master', RoleLabel::Conductor, 2),
            new PullRequestTarget('a/y', 'b2', 'master', RoleLabel::Docs, 1),
            new PullRequestTarget('a/z', 'b3', 'master', RoleLabel::Finalize, 3),
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Conductor before Docs before Finalize');
        $this->semiAuto($api, new FakeClock())->ship($targets);
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
        $this->primeFinalize($api);

        $this->semiAuto($api, new FakeClock())->ship($this->targets());

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
