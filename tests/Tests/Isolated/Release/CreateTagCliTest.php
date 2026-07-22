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
        // With RELEASE_APP_TOKEN in env and no --app-token flag, the CLI
        // must fall back to the env value and let the DTO construct
        // successfully. Failure then comes from the downstream GitHub API
        // call, not the DTO's "appToken is required".
        $process = new Process(
            [
                'php',
                self::BIN,
                '--repo=openemr/openemr',
                '--release-version=8.1.0',
                '--commit-sha=' . self::VALID_SHA,
                '--conductor-pr-url=https://github.com/openemr/openemr/pull/1',
                '--date=2026-07-21',
            ],
            null,
            ['RELEASE_APP_TOKEN' => 'ghs_fake_token_from_env'],
        );
        $process->run();

        // Guaranteed to fail (fake token / no network), but NOT with the
        // DTO's "appToken is required" message.
        self::assertFalse($process->isSuccessful());
        self::assertStringNotContainsString('appToken is required', $process->getOutput());
    }

    public function testTestFlagAccepted(): void
    {
        // --test is a VALUE_NONE flag; parse without error even though
        // the network call downstream will still fail.
        $process = new Process([
            'php',
            self::BIN,
            '--repo=openemr/openemr',
            '--release-version=8.1.0',
            '--commit-sha=' . self::VALID_SHA,
            '--conductor-pr-url=https://github.com/openemr/openemr/pull/1',
            '--app-token=t',
            '--date=2026-07-21',
            '--test',
        ]);
        $process->run();

        // Should NOT be an option-parse error.
        self::assertStringNotContainsString('does not exist', $process->getOutput());
        self::assertStringNotContainsString('does not exist', $process->getErrorOutput());
    }

    public function testGithubOutputEnvOptional(): void
    {
        // With GITHUB_OUTPUT unset, the CLI must still parse + attempt
        // its work without complaint about the missing env var (which
        // is only used to record step outputs when running under
        // GitHub Actions). We can't verify the file-write happens
        // without a real tag being created, but we can verify absence
        // of the env doesn't itself abort the process.
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
                '--date=2026-07-21',
            ]);
            $process->run();
        } finally {
            if ($env !== false) {
                putenv('GITHUB_OUTPUT=' . $env);
            }
        }

        // Failure is expected (network), but not from missing GITHUB_OUTPUT.
        $combined = $process->getOutput() . $process->getErrorOutput();
        self::assertStringNotContainsString('GITHUB_OUTPUT', $combined);
    }
}
