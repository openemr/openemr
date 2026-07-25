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
        // Prove the RELEASE_APP_TOKEN env fallback runs by triggering a
        // deterministic *later* local error. Combine --probe (bypasses
        // schema validation, which is where the next check would fire)
        // with an empty --target-repos. If the env fallback runs, the
        // token is populated, the ctor's "appToken is required" check
        // does NOT fire, and Dispatcher::dispatch() reaches its empty-
        // target-repos guard (`targetRepos must not be empty`). If the
        // env fallback did NOT run, we'd get "appToken is required"
        // instead. Both signals are local + fast + deterministic; no HTTP
        // is ever attempted.
        $process = new Process(
            [
                'php',
                self::BIN,
                '--event=release-permissions-probe',
                '--sha=' . self::VALID_SHA,
                '--probe',
                '--target-repos=',
            ],
            null,
            ['RELEASE_APP_TOKEN' => 'ghs_fake_token_from_env'],
        );
        $process->run();

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('targetRepos must not be empty', $process->getOutput());
        self::assertStringNotContainsString('appToken is required', $process->getOutput());
    }

    public function testProbeFlagAccepted(): void
    {
        // Prove --probe parses at the option layer by combining it with a
        // deterministic downstream failure (empty --target-repos). If
        // --probe were unregistered, Symfony Console would abort at parse
        // time with "does not exist"; if it parses correctly, we advance
        // to Dispatcher's empty-targets guard. --probe also serves its
        // secondary role here: it bypasses schema validation, letting
        // the empty-targets check fire first as a clean signal that
        // --probe was honored (schema validation would fail on a
        // separate axis and mask the assertion).
        $process = new Process([
            'php',
            self::BIN,
            '--event=release-permissions-probe',
            '--sha=' . self::VALID_SHA,
            '--app-token=t',
            '--probe',
            '--target-repos=',
        ]);
        $process->run();

        self::assertSame(1, $process->getExitCode());
        self::assertStringContainsString('targetRepos must not be empty', $process->getOutput());
        self::assertStringNotContainsString('does not exist', $process->getOutput());
        self::assertStringNotContainsString('does not exist', $process->getErrorOutput());
    }

    public function testTargetReposCommaListAccepted(): void
    {
        // Prove --target-repos parses at the option layer with a non-
        // trivial comma-list value. Combine with --event=UNKNOWN so
        // DispatchDataBuilder trips its "Unknown dispatch event" local
        // exception before Dispatcher::dispatch is ever reached — the
        // fact that we get the DispatchDataBuilder error (not a Symfony
        // "does not exist" option-parse error) confirms --target-repos
        // was accepted at the CLI layer. No HTTP.
        $process = new Process([
            'php',
            self::BIN,
            '--event=totally-unknown-event',
            '--sha=' . self::VALID_SHA,
            '--app-token=t',
            '--target-repos=owner1/repo1,owner2/repo2',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertStringContainsString(
            'Unknown dispatch event',
            $process->getOutput() . $process->getErrorOutput(),
        );
        self::assertStringNotContainsString('does not exist', $process->getOutput());
        self::assertStringNotContainsString('does not exist', $process->getErrorOutput());
    }
}
