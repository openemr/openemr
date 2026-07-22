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
 * Arg-shell tests for tools/release/bin/ship-release.php.
 *
 * ship-release.php delegates to ShipReleaseOrchestrator, which shells
 * out to `gh` for every downstream operation (including the preflight
 * snapshot every mode runs). The `gh` binary is not installed in the
 * isolated test container, so a happy-path invocation is unreachable
 * here. These tests exercise only the CLI's own validation gates that
 * fire BEFORE the orchestrator is constructed: required-flag rejection,
 * --mode enum validation, --timeout-seconds positive-integer check, and
 * accepting flag presence for --dry-run / --summary-file.
 */
final class ShipReleaseCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/ship-release.php';

    public function testMissingReleaseVersionRejected(): void
    {
        $process = new Process(['php', self::BIN, '--rel-branch=rel-810']);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--release-version and --rel-branch are required', $process->getOutput());
    }

    public function testMissingRelBranchRejected(): void
    {
        $process = new Process(['php', self::BIN, '--release-version=8.1.0']);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--release-version and --rel-branch are required', $process->getOutput());
    }

    public function testInvalidModeRejected(): void
    {
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--mode=turbo',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString(
            '--mode must be one of: dry-run, semi-auto, full-auto',
            $process->getOutput(),
        );
    }

    public function testNonDigitTimeoutRejected(): void
    {
        // ctype_digit rejects any non-digit character.
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--timeout-seconds=60s',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--timeout-seconds must be a positive integer', $process->getOutput());
    }

    public function testZeroTimeoutRejected(): void
    {
        // "0" is ctype_digit but fails the >= 1 check.
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--timeout-seconds=0',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--timeout-seconds must be a positive integer', $process->getOutput());
    }

    public function testNegativeTimeoutRejected(): void
    {
        // "-5" contains a non-digit ('-') so ctype_digit rejects it.
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--timeout-seconds=-5',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('--timeout-seconds must be a positive integer', $process->getOutput());
    }

    public function testDryRunOverridesModeSemantically(): void
    {
        // With --dry-run set, the CLI forces Mode::DryRun regardless of
        // --mode's value — including for the invalid "turbo" value that
        // would otherwise abort with the enum error. Confirm the enum
        // guard fires BEFORE the dry-run override so operators aren't
        // silently rescued from a typo, then confirm a valid --mode
        // combined with --dry-run reaches the orchestrator (proven by
        // the subsequent gh-not-found failure rather than any CLI-level
        // validation error).
        $processEnum = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--dry-run',
            '--mode=turbo',
        ]);
        $processEnum->run();
        self::assertStringContainsString(
            '--mode must be one of',
            $processEnum->getOutput(),
            'invalid --mode must still be rejected even with --dry-run present',
        );

        $processValid = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--dry-run',
            '--mode=full-auto',
        ]);
        $processValid->run();
        // Should NOT be any CLI-level validation error — the orchestrator
        // takes over and fails on the missing gh binary.
        $combined = $processValid->getOutput() . $processValid->getErrorOutput();
        self::assertStringNotContainsString('--mode must be one of', $combined);
        self::assertStringNotContainsString('--release-version and --rel-branch are required', $combined);
        self::assertStringNotContainsString('--timeout-seconds must be a positive integer', $combined);
    }

    public function testSummaryFileFlagAcceptedBothSetAndUnset(): void
    {
        // Regression against removing/renaming the option. Set variant.
        $processSet = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--summary-file=/tmp/summary.md',
        ]);
        $processSet->run();
        $combinedSet = $processSet->getOutput() . $processSet->getErrorOutput();
        self::assertStringNotContainsString('does not exist', $combinedSet);

        // Unset variant — --summary-file has a '' default.
        $processUnset = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
        ]);
        $processUnset->run();
        $combinedUnset = $processUnset->getOutput() . $processUnset->getErrorOutput();
        self::assertStringNotContainsString('does not exist', $combinedUnset);
    }
}
