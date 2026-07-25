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
 * Arg-shell tests for tools/release/bin/create-tag.php.
 *
 * create-tag.php POSTs to the GitHub API to mint an annotated tag, so a
 * happy-path invocation would hit the network. These tests exercise the
 * TagCreationRequest DTO's validation gates (version shape, SHA shape,
 * date shape, non-empty app-token, owner/name repo) as reached through
 * the CLI's option-parsing layer, plus the RELEASE_APP_TOKEN env
 * fallback and the $GITHUB_OUTPUT env optionality.
 */
final class CreateTagCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/create-tag.php';

    private const VALID_SHA = 'a1b2c3d4e5f60718293a4b5c6d7e8f9012345678';

    public function testMalformedShaRejected(): void
    {
        // TagCreationRequest::__construct throws
        // InvalidArgumentException on a non-40-hex SHA; the CLI catches
        // and exits 2.
        $process = new Process([
            'php',
            self::BIN,
            '--repo=openemr/openemr',
            '--release-version=8.1.0',
            '--commit-sha=notasha',
            '--conductor-pr-url=https://github.com/openemr/openemr/pull/1',
            '--app-token=t',
            '--date=2026-07-21',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('commitSha must be 40 hex characters', $process->getOutput());
    }

    public function testMalformedVersionRejected(): void
    {
        // Non-N.N.N version rejected by TagCreationRequest.
        $process = new Process([
            'php',
            self::BIN,
            '--repo=openemr/openemr',
            '--release-version=v8.1.0',
            '--commit-sha=' . self::VALID_SHA,
            '--conductor-pr-url=https://github.com/openemr/openemr/pull/1',
            '--app-token=t',
            '--date=2026-07-21',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('version must be MAJOR.MINOR.PATCH', $process->getOutput());
    }

    public function testMissingRepoRejected(): void
    {
        // Empty --repo fails the DTO's owner/name pattern.
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--commit-sha=' . self::VALID_SHA,
            '--conductor-pr-url=https://github.com/openemr/openemr/pull/1',
            '--app-token=t',
            '--date=2026-07-21',
        ]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('repo must be owner/name', $process->getOutput());
    }

    public function testMissingAppTokenRejectedWhenEnvUnset(): void
    {
        // With no --app-token and no RELEASE_APP_TOKEN env, the DTO
        // rejects the empty token.
        $env = getenv('RELEASE_APP_TOKEN');
        putenv('RELEASE_APP_TOKEN');
        try {
            $process = new Process([
                'php',
                self::BIN,
                '--repo=openemr/openemr',
                '--release-version=8.1.0',
                '--commit-sha=' . self::VALID_SHA,
                '--conductor-pr-url=https://github.com/openemr/openemr/pull/1',
                '--date=2026-07-21',
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

    public function testReleaseAppTokenEnvFallbackAccepted(): void
    {
        // Prove the RELEASE_APP_TOKEN env fallback runs by triggering a
        // deterministic *later* DTO validation error. Pass a malformed
        // --date (fails TagCreationRequest's `date must be ISO YYYY-MM-DD`
        // check) with the env token supplied but no --app-token flag.
        // - If env fallback DID run: token is populated, ctor gets past
        //   the "appToken is required" check, and the malformed --date
        //   trips the date-format check next → exit 2 with the ISO error.
        // - If env fallback did NOT run: token stays empty, the ctor's
        //   "appToken is required" check fires first → different error.
        // No HTTP client is ever constructed either way; failure is local
        // + fast + deterministic.
        $process = new Process(
            [
                'php',
                self::BIN,
                '--repo=openemr/openemr',
                '--release-version=8.1.0',
                '--commit-sha=' . self::VALID_SHA,
                '--conductor-pr-url=https://github.com/openemr/openemr/pull/1',
                '--date=BAD',
            ],
            null,
            ['RELEASE_APP_TOKEN' => 'ghs_fake_token_from_env'],
        );
        $process->run();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('date must be ISO YYYY-MM-DD', $process->getOutput());
        self::assertStringNotContainsString('appToken is required', $process->getOutput());
    }

    public function testTestFlagAccepted(): void
    {
        // Prove --test parses at the option layer by combining it with a
        // deterministic downstream failure (malformed --date). If --test
        // were unregistered, Symfony Console would abort at parse time
        // with "does not exist"; if --test parses correctly, the DTO's
        // date-format check trips next → predictable exit 2. No HTTP.
        $process = new Process([
            'php',
            self::BIN,
            '--repo=openemr/openemr',
            '--release-version=8.1.0',
            '--commit-sha=' . self::VALID_SHA,
            '--conductor-pr-url=https://github.com/openemr/openemr/pull/1',
            '--app-token=t',
            '--date=BAD',
            '--test',
        ]);
        $process->run();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('date must be ISO YYYY-MM-DD', $process->getOutput());
        self::assertStringNotContainsString('does not exist', $process->getOutput());
        self::assertStringNotContainsString('does not exist', $process->getErrorOutput());
    }

    public function testGithubOutputEnvOptional(): void
    {
        // GITHUB_OUTPUT is only consulted after tag creation succeeds. To
        // prove its absence doesn't abort the process pre-DTO, combine
        // unset GITHUB_OUTPUT with a deterministic DTO-level failure
        // (malformed --date). If unset GITHUB_OUTPUT tripped anything
        // earlier, we'd see a different failure signature. Confirms the
        // env variable's absence is quietly tolerated at the boundary
        // this test cares about.
        $env = getenv('GITHUB_OUTPUT');
        putenv('GITHUB_OUTPUT');
        try {
            $process = new Process([
                'php',
                self::BIN,
                '--repo=openemr/openemr',
                '--release-version=8.1.0',
                '--commit-sha=' . self::VALID_SHA,
                '--conductor-pr-url=https://github.com/openemr/openemr/pull/1',
                '--app-token=t',
                '--date=BAD',
            ]);
            $process->run();
        } finally {
            if ($env !== false) {
                putenv('GITHUB_OUTPUT=' . $env);
            }
        }

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('date must be ISO YYYY-MM-DD', $process->getOutput());
        $combined = $process->getOutput() . $process->getErrorOutput();
        self::assertStringNotContainsString('GITHUB_OUTPUT', $combined);
    }
}
