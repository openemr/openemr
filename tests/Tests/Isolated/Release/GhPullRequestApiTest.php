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
}
