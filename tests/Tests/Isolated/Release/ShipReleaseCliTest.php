<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
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

    public function testInvalidModeRejectedEvenWithDryRunFlag(): void
    {
        // Confirm the --mode enum guard fires BEFORE the --dry-run
        // override so operators aren't silently rescued from a --mode
        // typo. Local + deterministic — no external processes involved.
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--dry-run',
            '--mode=turbo',
        ]);
        $process->run();

        self::assertStringContainsString(
            '--mode must be one of',
            $process->getOutput(),
            'invalid --mode must still be rejected even with --dry-run present',
        );
    }

    public function testValidModeCombinedWithDryRunPassesCliValidation(): void
    {
        // Prove valid --mode + --dry-run passes the CLI's option layer by
        // adding a *later* deterministic local failure (invalid
        // --timeout-seconds). If --mode were the failing gate, we'd see
        // "--mode must be one of"; instead we see the timeout-integer
        // error, which is the next validation step. No external process
        // ever starts; the orchestrator is never constructed.
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--dry-run',
            '--mode=full-auto',
            '--timeout-seconds=0',
        ]);
        $process->run();

        self::assertStringContainsString(
            '--timeout-seconds must be a positive integer',
            $process->getOutput(),
        );
        self::assertStringNotContainsString('--mode must be one of', $process->getOutput());
        self::assertStringNotContainsString(
            '--release-version and --rel-branch are required',
            $process->getOutput(),
        );
    }

    public function testSummaryFileFlagAcceptedBothSetAndUnset(): void
    {
        // Prove --summary-file parses at the option layer (both when
        // supplied and when omitted) by adding a *later* deterministic
        // local failure (invalid --timeout-seconds). If --summary-file
        // were unregistered, Symfony Console would abort at parse time
        // with "does not exist"; if it parses correctly, we advance to
        // the timeout check. The orchestrator is never constructed and
        // no external process runs.

        // Set variant — --summary-file supplied.
        $processSet = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--summary-file=/tmp/summary.md',
            '--timeout-seconds=0',
        ]);
        $processSet->run();
        $combinedSet = $processSet->getOutput() . $processSet->getErrorOutput();
        self::assertStringContainsString(
            '--timeout-seconds must be a positive integer',
            $processSet->getOutput(),
        );
        self::assertStringNotContainsString('does not exist', $combinedSet);

        // Unset variant — --summary-file has a '' default.
        $processUnset = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--rel-branch=rel-810',
            '--timeout-seconds=0',
        ]);
        $processUnset->run();
        $combinedUnset = $processUnset->getOutput() . $processUnset->getErrorOutput();
        self::assertStringContainsString(
            '--timeout-seconds must be a positive integer',
            $processUnset->getOutput(),
        );
        self::assertStringNotContainsString('does not exist', $combinedUnset);
    }
}
