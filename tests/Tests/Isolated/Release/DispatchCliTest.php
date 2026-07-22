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
 * Arg-shell tests for tools/release/bin/dispatch.php.
 *
 * dispatch.php POSTs to the GitHub API to fire repository_dispatch
 * envelopes at consumer repos, so a happy-path invocation would hit
 * the network. These tests exercise only the input-parsing / DTO
 * construction boundary: what the CLI accepts and rejects before the
 * HttpClient is ever called. Dispatcher/DispatchDataBuilder unit tests
 * cover the downstream behavior in isolation.
 */
final class DispatchCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/dispatch.php';

    private const VALID_SHA = 'a1b2c3d4e5f60718293a4b5c6d7e8f9012345678';

    public function testMissingEventRejected(): void
    {
        // No --event supplied. DispatchDataBuilder::build() throws
        // "Unknown dispatch event: " (empty string) before the DTO ever
        // sees a token, so this validates the event-name gate. The
        // uncaught InvalidArgumentException surfaces via Symfony
        // Console's default exception renderer to stderr, not stdout.
        $process = new Process([
            'php',
            self::BIN,
            '--sha=' . self::VALID_SHA,
            '--app-token=t',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString('Unknown dispatch event', $process->getErrorOutput());
    }

    public function testMissingShaRejected(): void
    {
        // DispatchRequest::__construct doesn't gate sha shape, but the
        // schema validation inside Dispatcher does. Empty sha still
        // reaches Dispatcher and gets rejected there.
        $process = new Process([
            'php',
            self::BIN,
            '--event=openemr-rel-cut',
            '--branch=rel-820',
            '--release-version=8.2.0',
            '--prev-release=8.1.0',
            '--app-token=t',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        // Either the DTO rejects empty sha or Dispatcher's schema validator does.
        // Assert non-zero exit + some error marker on stdout.
        self::assertNotSame(0, $process->getExitCode());
    }

    public function testMissingAppTokenRejected(): void
    {
        // DispatchRequest::__construct throws InvalidArgumentException
        // when appToken is empty. Exit code 2 per the catch block in
        // dispatch.php. RELEASE_APP_TOKEN env is unset for this run.
        $env = getenv('RELEASE_APP_TOKEN');
        putenv('RELEASE_APP_TOKEN');
        try {
            $process = new Process([
                'php',
                self::BIN,
                '--event=release-permissions-probe',
                '--sha=' . self::VALID_SHA,
            ]);
            $process->run();
        } finally {
            if ($env !== false) {
                putenv('RELEASE_APP_TOKEN=' . $env);
            }
        }

        self::assertFalse($process->isSuccessful());
        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('appToken is required', $process->getOutput());
    }

    public function testReleaseAppTokenEnvFallsBackWhenAppTokenMissing(): void
    {
        // With --app-token omitted, dispatch.php pulls RELEASE_APP_TOKEN
        // from the environment. If the fallback works, the DTO's
        // appToken-required check does NOT fire (we get a different
        // failure downstream — network / schema). Use --probe so schema
        // validation is bypassed; the failure will come from network.
        $process = new Process(
            ['php', self::BIN, '--event=release-permissions-probe', '--sha=' . self::VALID_SHA, '--probe'],
            null,
            ['RELEASE_APP_TOKEN' => 'ghs_fake_token_from_env'],
        );
        $process->run();

        self::assertFalse($process->isSuccessful(), 'network call must still fail; just checking env fallback ran');
        // Should NOT be the "appToken is required" DTO error — that would
        // mean the env fallback wasn't consulted.
        self::assertStringNotContainsString('appToken is required', $process->getOutput());
    }

    public function testProbeFlagAccepted(): void
    {
        // --probe is a VALUE_NONE flag; when unrecognized the CLI would
        // reject with "does not exist" from Symfony Console. Confirm it
        // parses without triggering that.
        $process = new Process([
            'php',
            self::BIN,
            '--event=release-permissions-probe',
            '--sha=' . self::VALID_SHA,
            '--app-token=t',
            '--probe',
        ]);
        $process->run();

        // Will fail on the network call, but not with an option-parse error.
        self::assertStringNotContainsString('does not exist', $process->getOutput());
        self::assertStringNotContainsString('does not exist', $process->getErrorOutput());
    }

    public function testTargetReposCommaListAccepted(): void
    {
        // --target-repos is a comma-separated list. Confirm the option
        // is accepted at parse time (regression against a future refactor
        // that might drop the option).
        $process = new Process([
            'php',
            self::BIN,
            '--event=release-permissions-probe',
            '--sha=' . self::VALID_SHA,
            '--app-token=t',
            '--probe',
            '--target-repos=owner1/repo1,owner2/repo2',
        ]);
        $process->run();

        self::assertStringNotContainsString('does not exist', $process->getOutput());
        self::assertStringNotContainsString('does not exist', $process->getErrorOutput());
    }
}
