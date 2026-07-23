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

use OpenEMR\Release\GitHubApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Retry-loop tests for GitHubApi::runGh(). Uses an anonymous subclass to
 * override the two test seams (createProcess + backoff) so the retry
 * behavior can be exercised without a real `gh` binary on PATH and
 * without real sleep between attempts.
 *
 * These tests do NOT exercise real gh api calls — the class's downstream
 * callers (ChangelogGenerator, PreflightChecker, etc.) do that via
 * higher-level integration tests. This file is scoped to the retry logic
 * only, since that's the piece that gets to decide whether a single
 * transient failure aborts the whole release-prep dispatch.
 */
final class GitHubApiTest extends TestCase
{
    public function testSuccessfulFirstAttemptReturnsStdoutAndSkipsBackoff(): void
    {
        $api = $this->makeApi([
            ['exit' => 0, 'out' => '[{"number":42}]', 'err' => ''],
        ]);

        $result = $api->prsForCommits(['abc123abc123abc123abc123abc123abc123abc1']);

        self::assertCount(1, $result);
        self::assertSame(42, $result[0]['number']);
        self::assertSame(0, $api->backoffCalls, 'no retry needed → no backoff');
        self::assertCount(1, $api->capturedCommands);
    }

    public function testTransientFailureRetriedAndSecondAttemptSucceeds(): void
    {
        // First attempt: the exact failure signature from the smoketest
        // (jq exits 1 with "unexpected end of JSON input" when gh's
        // response body was empty). Second attempt: real response.
        $api = $this->makeApi([
            ['exit' => 1, 'out' => '', 'err' => 'unexpected end of JSON input'],
            ['exit' => 0, 'out' => '[{"number":42,"title":"x","labels":[],"url":"u","author":"a"}]', 'err' => ''],
        ]);

        $result = $api->prsForCommits(['abc123abc123abc123abc123abc123abc123abc1']);

        self::assertCount(1, $result);
        self::assertSame(42, $result[0]['number']);
        self::assertSame(1, $api->backoffCalls, 'one retry → one backoff');
        self::assertSame([1], $api->backoffAttempts, 'backoff called with attempt=1 after first failure');
        self::assertCount(2, $api->capturedCommands);
    }

    public function testTwoTransientFailuresThenSuccessRetriesTwice(): void
    {
        $api = $this->makeApi([
            ['exit' => 1, 'out' => '', 'err' => 'unexpected end of JSON input'],
            ['exit' => 1, 'out' => '', 'err' => 'API rate limit exceeded'],
            ['exit' => 0, 'out' => '[]', 'err' => ''],
        ]);

        $result = $api->prsForCommits(['abc123abc123abc123abc123abc123abc123abc1']);

        self::assertSame([], $result);
        self::assertSame(2, $api->backoffCalls);
        self::assertSame([1, 2], $api->backoffAttempts, 'backoff attempts are 1s then 2s (linear, not exponential)');
        self::assertCount(3, $api->capturedCommands);
    }

    public function testAllAttemptsFailThrowsWithLastError(): void
    {
        $api = $this->makeApi([
            ['exit' => 1, 'out' => '', 'err' => 'first failure'],
            ['exit' => 1, 'out' => '', 'err' => 'second failure'],
            ['exit' => 1, 'out' => '', 'err' => 'third and final failure'],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('gh call failed after 3 attempts');
        $this->expectExceptionMessage('third and final failure');

        try {
            $api->prsForCommits(['abc123abc123abc123abc123abc123abc123abc1']);
        } finally {
            self::assertSame(2, $api->backoffCalls, 'backoff fires between attempts but not after the final failure');
            self::assertCount(3, $api->capturedCommands);
        }
    }

    public function testMaxAttemptsIsRespected(): void
    {
        // Explicit maxAttempts=2 → only one retry after the first failure.
        $api = $this->makeApi(
            [
                ['exit' => 1, 'out' => '', 'err' => 'first'],
                ['exit' => 1, 'out' => '', 'err' => 'second'],
            ],
            maxAttempts: 2,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('after 2 attempts');

        try {
            $api->prsForCommits(['abc123abc123abc123abc123abc123abc123abc1']);
        } finally {
            self::assertSame(1, $api->backoffCalls);
            self::assertCount(2, $api->capturedCommands);
        }
    }

    public function testMissingStderrFallsBackToExitCodeInErrorMessage(): void
    {
        // gh can exit non-zero without writing to stderr (rare but
        // possible — e.g., signal termination). Verify the fallback
        // error message includes the exit code so the failure isn't a
        // total black box.
        $api = $this->makeApi([
            ['exit' => 137, 'out' => '', 'err' => ''],
            ['exit' => 137, 'out' => '', 'err' => ''],
            ['exit' => 137, 'out' => '', 'err' => ''],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('exit 137');

        $api->prsForCommits(['abc123abc123abc123abc123abc123abc123abc1']);
    }

    public function testTimeoutThrownByProcessRunIsRetriedAsFailure(): void
    {
        // Symfony Process's 60s default timeout throws ProcessTimedOutException
        // rather than returning a failed exit — the retry loop must catch it
        // and treat it as a retryable attempt.
        $api = $this->makeApi([
            ['timeout' => true],
            ['exit' => 0, 'out' => '[]', 'err' => ''],
        ]);

        $result = $api->prsForCommits(['abc123abc123abc123abc123abc123abc123abc1']);

        self::assertSame([], $result);
        self::assertSame(1, $api->backoffCalls, 'timeout counted as retryable failure');
        self::assertCount(2, $api->capturedCommands);
    }

    public function testAllTimeoutsExhaustAttemptsAndSurfaceTimeoutInError(): void
    {
        $api = $this->makeApi([
            ['timeout' => true],
            ['timeout' => true],
            ['timeout' => true],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('timeout:');

        try {
            $api->prsForCommits(['abc123abc123abc123abc123abc123abc123abc1']);
        } finally {
            self::assertSame(2, $api->backoffCalls);
            self::assertCount(3, $api->capturedCommands);
        }
    }

    public function testPrsForCommitsBatchesSHAsIntoOneRequestPerChunk(): void
    {
        // 30 SHAs → 2 batches (25 + 5) → 2 gh requests instead of 30.
        // This is the primary defense against release-App rate-limit
        // exhaustion; see docs/release-mechanism-gaps.md G30.
        $api = $this->makeApi([
            ['exit' => 0, 'out' => '[{"number":1,"title":"a","labels":[],"url":"u1","author":"x"}]', 'err' => ''],
            ['exit' => 0, 'out' => '[{"number":2,"title":"b","labels":[],"url":"u2","author":"y"}]', 'err' => ''],
        ]);

        $shas = array_fill(0, 30, str_repeat('a', 40));
        $result = $api->prsForCommits($shas);

        self::assertCount(2, $result, 'both batches contributed one PR each');
        self::assertCount(2, $api->capturedCommands, '30 SHAs / batch-size 25 → 2 requests');
    }

    public function testPrsForCommitsSingleBatchWhenUnderChunkSize(): void
    {
        // 20 SHAs fits in one batch — verifies chunk boundary.
        $api = $this->makeApi([
            ['exit' => 0, 'out' => '[{"number":1,"title":"a","labels":[],"url":"u1","author":"x"}]', 'err' => ''],
        ]);

        $shas = array_fill(0, 20, str_repeat('a', 40));
        $result = $api->prsForCommits($shas);

        self::assertCount(1, $result);
        self::assertCount(1, $api->capturedCommands);
    }

    public function testPrsForCommitsDeduplicatesPRAcrossBatches(): void
    {
        // Same PR (#42) appears in both batches (e.g., a PR with commits
        // spread across the 25-SHA batch boundary). Dedup by PR number
        // must survive the batch split.
        $api = $this->makeApi([
            ['exit' => 0, 'out' => '[{"number":42,"title":"t","labels":[],"url":"u","author":"x"}]', 'err' => ''],
            ['exit' => 0, 'out' => '[{"number":42,"title":"t","labels":[],"url":"u","author":"x"}]', 'err' => ''],
        ]);

        $shas = array_fill(0, 30, str_repeat('a', 40));
        $result = $api->prsForCommits($shas);

        self::assertCount(1, $result, 'PR #42 appears once despite being in both batches');
        self::assertSame(42, $result[0]['number']);
    }

    public function testPrsForCommitsBatchCommandTargetsSearchIssuesWithShaQualifiers(): void
    {
        // Concrete-command assertion: verifies the batch call actually
        // targets /search/issues and includes sha: qualifiers joined by
        // '+' (URL-encoded search-query separator).
        $api = $this->makeApi([
            ['exit' => 0, 'out' => '[]', 'err' => ''],
        ]);

        $api->prsForCommits([
            'abc123abc123abc123abc123abc123abc123abc1',
            'def456def456def456def456def456def456def4',
        ]);

        $cmd = $api->capturedCommands[0];
        self::assertContains('api', $cmd);
        // Find the URL argument (position after 'api')
        $urlArg = $cmd[array_search('api', $cmd, true) + 1];
        self::assertStringContainsString('/search/issues?q=', $urlArg);
        self::assertStringContainsString('repo:openemr/openemr', $urlArg);
        self::assertStringContainsString('type:pr', $urlArg);
        self::assertStringContainsString('is:merged', $urlArg);
        self::assertStringContainsString('sha:abc123abc123abc123abc123abc123abc123abc1', $urlArg);
        self::assertStringContainsString('sha:def456def456def456def456def456def456def4', $urlArg);
    }

    public function testConstructorRejectsNonPositiveMaxAttempts(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maxAttempts must be >= 1');

        new GitHubApi('openemr/openemr', 0);
    }

    /**
     * @param list<array{exit: int, out: string, err: string}|array{timeout: true}> $responses
     */
    private function makeApi(array $responses, int $maxAttempts = 3): GitHubApiTestDouble
    {
        return new GitHubApiTestDouble('openemr/openemr', $maxAttempts, $responses);
    }
}

/**
 * Subclass with the retry loop's two test seams overridden:
 * createProcess() fabricates a Process whose runtime behavior is
 * pre-configured (via Process::fromShellCommandline against a canned
 * printf/exit); backoff() records the call without sleeping.
 *
 * Kept in the same file to avoid a two-file split for a single test's
 * fixture — mirrors the ExtractChangelogSectionCliTest / SummaryCliTest
 * convention of self-contained isolated tests. Not exported.
 */
final class GitHubApiTestDouble extends GitHubApi
{
    public int $backoffCalls = 0;

    /** @var list<int> */
    public array $backoffAttempts = [];

    /** @var list<list<string>> */
    public array $capturedCommands = [];

    /**
     * @param list<array{exit: int, out: string, err: string}|array{timeout: true}> $responses
     */
    public function __construct(string $repo, int $maxAttempts, private array $responses)
    {
        parent::__construct($repo, $maxAttempts);
    }

    /**
     * @param list<string> $command
     */
    protected function createProcess(array $command): Process
    {
        $this->capturedCommands[] = $command;

        $response = array_shift($this->responses);
        if ($response === null) {
            throw new \LogicException(
                'GitHubApiTestDouble ran out of pre-configured responses; test setup mismatch',
            );
        }

        // Timeout scenario — return a real Process configured with an
        // absurdly small timeout against a sleep, so run() actually throws
        // ProcessTimedOutException. Not overridable via subclass because
        // Process::run() is @final. Uses `sleep 60` since a 0.05s timeout
        // will always trip before sleep completes.
        if (isset($response['timeout'])) {
            $timeoutProcess = Process::fromShellCommandline('sleep 60');
            $timeoutProcess->setTimeout(0.05);
            return $timeoutProcess;
        }

        // Fabricate a Process whose ->run() will exhibit the configured
        // exit code + stdout + stderr. Uses fromShellCommandline so we
        // can compose the three via a small `printf ...; printf ... 1>&2;
        // exit N` script — no dependency on gh, jq, or a network. The
        // resulting Process is a real subprocess; the retry loop can't
        // tell the difference between this and a real gh invocation.
        $shell = sprintf(
            'printf %%s %s; printf %%s %s 1>&2; exit %d',
            escapeshellarg($response['out']),
            escapeshellarg($response['err']),
            $response['exit'],
        );
        return Process::fromShellCommandline($shell);
    }

    protected function backoff(int $attempt): void
    {
        $this->backoffCalls++;
        $this->backoffAttempts[] = $attempt;
    }
}
