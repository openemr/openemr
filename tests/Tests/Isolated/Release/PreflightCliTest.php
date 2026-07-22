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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Arg-shell tests for tools/release/bin/preflight.php.
 *
 * preflight.php shells out to `gh` for milestone + GHSA checks, so the
 * happy path is untestable in an isolated context. These tests verify
 * only the CLI's option-parsing gates: what combinations of --milestone
 * / --skip-milestone / --repo are accepted or rejected before any gh
 * process spawns.
 */
final class PreflightCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/preflight.php';

    public function testMissingMilestoneRejectedWhenSkipMilestoneNotSet(): void
    {
        // Without --milestone and without --skip-milestone, the checker
        // aborts before spawning gh so no external state is required.
        $process = new Process([
            'php',
            self::BIN,
            '--skip-ghsa',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--milestone is required', $process->getOutput());
    }

    public function testBothSkipFlagsAccepted(): void
    {
        // With both --skip-milestone and --skip-ghsa the checker has
        // nothing to do and exits cleanly. This is the workflow's
        // "dry-run of the CLI itself" mode.
        $process = new Process([
            'php',
            self::BIN,
            '--skip-milestone',
            '--skip-ghsa',
        ]);
        $process->run();

        self::assertTrue($process->isSuccessful(), 'stdout: ' . $process->getOutput() . ' stderr: ' . $process->getErrorOutput());
        self::assertSame(0, $process->getExitCode());
    }

    public function testEmptyRepoRejected(): void
    {
        // --repo is required to be non-empty when provided; Symfony's
        // VALUE_REQUIRED lets `--repo=` through as an empty string
        // which the CLI itself must reject.
        $process = new Process([
            'php',
            self::BIN,
            '--repo=',
            '--skip-milestone',
            '--skip-ghsa',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--repo must be a non-empty string', $process->getOutput());
    }
}
