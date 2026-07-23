<?php

/**
 * Wrapper around the gh CLI for GitHub API calls.
 *
 * All public methods route through runGh(), which retries transient
 * failures (non-zero exit from a single `gh api ...` invocation).
 * ChangelogMutator processes hundreds of commits per release cycle;
 * a single sporadic gh/jq flake (rate-limit tickle, brief 5xx, empty
 * response body that jq errors out on with "unexpected end of JSON
 * input") shouldn't abort the whole release-prep dispatch.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class GitHubApi
{
    /**
     * Number of times runGh() will attempt a `gh api` invocation before
     * giving up. Overridable via constructor for tests. 3 attempts with
     * 1s + 2s backoff absorbs the transient-flake shapes we see in
     * practice without adding meaningful latency to the happy path.
     */
    protected int $maxAttempts;

    public function __construct(
        private readonly string $repo = 'openemr/openemr',
        int $maxAttempts = 3,
    ) {
        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('maxAttempts must be >= 1');
        }
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * Call a GitHub API endpoint via gh CLI and return decoded JSON.
     *
     * @return list<array<string, mixed>>
     */
    public function paginate(string $endpoint): array
    {
        $output = $this->runGh([
            'api', '--paginate', '--slurp',
            "/repos/{$this->repo}{$endpoint}",
        ]);

        $pages = json_decode($output, true);
        if (!is_array($pages)) {
            throw new \RuntimeException("Failed to parse JSON from gh api for {$endpoint}");
        }

        // --slurp wraps each page in an outer array: [[...page1...], [...page2...]]
        /** @var list<list<array<string, mixed>>> $pages */
        return array_merge(...$pages);
    }

    /**
     * Find a milestone number by its name.
     *
     * Search open milestones first, then closed.
     */
    public function findMilestone(string $name): int
    {
        foreach (['open', 'closed'] as $state) {
            $milestones = $this->paginate("/milestones?state={$state}&per_page=100");
            foreach ($milestones as $milestone) {
                if ($milestone['title'] === $name) {
                    /** @var int $number */
                    $number = $milestone['number'];
                    return $number;
                }
            }
        }

        throw new \RuntimeException("Milestone not found: {$name}");
    }

    /**
     * Fetch all closed issues for a milestone.
     *
     * @return list<array<string, mixed>>
     */
    public function closedIssuesForMilestone(int $milestoneNumber): array
    {
        return $this->paginate("/issues?milestone={$milestoneNumber}&state=closed&per_page=100");
    }

    /**
     * Get all commit SHAs between two refs using the compare API.
     *
     * @return list<string>
     */
    public function commitsBetweenRefs(string $base, string $head): array
    {
        $endpoint = "/repos/{$this->repo}/compare/{$base}...{$head}";
        $shas = [];
        $page = 1;
        $maxPages = 50;

        do {
            $output = trim($this->runGh([
                'api',
                "{$endpoint}?per_page=250&page={$page}",
                '--jq', '.commits[].sha',
            ]));

            if ($output === '') {
                break;
            }

            $pageShas = explode("\n", $output);
            $shas = array_merge($shas, $pageShas);
            $page++;
        } while (count($pageShas) === 250 && $page <= $maxPages);

        if ($page > $maxPages) {
            throw new \RuntimeException(
                "Compare {$base}...{$head} exceeded {$maxPages} pages — results may be truncated",
            );
        }

        return $shas;
    }

    /**
     * Batch size for `gh pr list --search` multi-SHA queries. 25 SHAs per
     * batch reduces a typical 200-commit changelog build from ~200
     * requests to ~8 — the primary defense against release-App-installation
     * rate-limit exhaustion (see docs/release-mechanism-gaps.md G30).
     *
     * NOTE on why this uses `gh pr list --search` (GraphQL under the hood)
     * rather than the REST `/search/issues` endpoint: `/search/issues` has
     * a 256-char query limit, which caps at 4-5 SHAs per batch — 5x worse
     * throughput. `gh pr list --search` uses GraphQL, no such limit.
     */
    private const PRS_FOR_COMMITS_BATCH_SIZE = 25;

    /**
     * Resolve commit SHAs to their associated pull requests.
     *
     * Deduplicates by PR number. `author` is the PR user's login (e.g.
     * `dependabot[bot]`), needed by ChangelogGenerator's noise filter to
     * distinguish dependabot/release-bot PRs.
     *
     * Batches SHAs into `gh pr list --search` queries so a typical
     * 200-commit changelog build makes ~8 requests instead of ~200.
     *
     * Author-format translation: `gh pr list --json author` returns
     * `app/dependabot` for bot users, but the REST commits-pulls endpoint
     * (and thus ChangelogGenerator's `DEPENDABOT` / `RELEASE_BOT`
     * constants) uses the `dependabot[bot]` shape. Any login starting
     * with `app/` is normalized here so the noise filter continues to
     * match without any downstream changes.
     *
     * @param list<string> $shas
     * @return list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}>
     */
    public function prsForCommits(array $shas): array
    {
        /** @var array<int, array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}> $seen */
        $seen = [];

        foreach (array_chunk($shas, self::PRS_FOR_COMMITS_BATCH_SIZE) as $batch) {
            $searchTerm = implode(' OR ', array_map(
                static fn(string $sha): string => "sha:{$sha}",
                $batch,
            ));
            // `.author.login // ""` defaults to empty string when the PR
            // author is null (deleted GitHub user) — otherwise jq would
            // return literal null and the normalization below would
            // TypeError. Empty string won't match any noise-filter
            // constant, so a deleted-author PR would be included in the
            // changelog rather than dropped as noise (correct default).
            $output = $this->runGh([
                'pr', 'list',
                '--repo', $this->repo,
                '--state', 'merged',
                '--search', $searchTerm,
                '--json', 'number,title,labels,url,author',
                '--limit', '100',
                '--jq', '[.[] | {number, title, labels: [.labels[] | {name}], url, author: (.author.login // "")}]',
            ]);

            /** @var list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}> $prs */
            $prs = json_decode($output, true) ?? [];
            foreach ($prs as $pr) {
                if (str_starts_with($pr['author'], 'app/')) {
                    $pr['author'] = substr($pr['author'], 4) . '[bot]';
                }
                if (!isset($seen[$pr['number']])) {
                    $seen[$pr['number']] = $pr;
                }
            }
        }

        return array_values($seen);
    }

    /**
     * Fetch all published security advisories.
     *
     * The `state=published` query param filters at the API level; the
     * post-filter here is a belt-and-suspenders guard so a stray draft
     * or withdrawn advisory can never leak into a rendered CHANGELOG
     * Security section if the API-side filter regresses.
     *
     * @return list<array<string, mixed>>
     */
    public function publishedAdvisories(): array
    {
        $all = $this->paginate('/security-advisories?state=published&per_page=100');
        return array_values(array_filter(
            $all,
            static fn (array $advisory): bool => ($advisory['state'] ?? '') === 'published',
        ));
    }

    /**
     * Invoke `gh $args` with retry-on-transient-failure. Returns stdout on
     * success; throws \RuntimeException with the last error message after
     * $maxAttempts failed attempts.
     *
     * A "failed attempt" is any non-zero exit code. jq's
     * "unexpected end of JSON input" (which is what surfaces when gh's
     * response body is empty because of a rate-limit tickle / brief 5xx /
     * transient network hiccup) fits that shape — jq exits 1 when its
     * stdin can't be parsed. Retrying absorbs those flakes without hiding
     * sustained failures (all $maxAttempts fail → throw).
     *
     * Zero exit + empty stdout is intentionally NOT treated as a retryable
     * signal here: `commitsBetweenRefs()` relies on empty stdout as its
     * natural pagination terminator (`--jq '.commits[].sha'` outputs empty
     * when the compare page returns no commits), and forcing a retry there
     * would degrade the happy-path terminator into three wasted attempts
     * per pagination-end.
     *
     * Backoff is $attempt seconds between attempts (1s after attempt 1,
     * 2s after attempt 2). Total worst-case added latency at $maxAttempts=3
     * is 3 seconds, which is a rounding error against the process runtime
     * of ChangelogMutator's typical release-cycle work.
     *
     * @param list<string> $args
     */
    protected function runGh(array $args): string
    {
        $lastError = 'unknown';
        for ($attempt = 1; $attempt <= $this->maxAttempts; $attempt++) {
            $process = $this->createProcess(['gh', ...$args]);
            try {
                $process->run();
            } catch (ProcessTimedOutException $e) {
                // Symfony Process's 60s default timeout throws instead of
                // returning a failed exit; treat it as a retryable failure
                // so a stalled `gh api` (upstream 502 that never returns
                // a body, etc.) doesn't skip the backoff/retry path.
                $lastError = sprintf('timeout: %s', $e->getMessage());
                if ($attempt < $this->maxAttempts) {
                    $this->backoff($attempt);
                }
                continue;
            }
            if ($process->isSuccessful()) {
                return $process->getOutput();
            }
            $stderr = trim($process->getErrorOutput());
            $lastError = $stderr !== ''
                ? $stderr
                : sprintf('exit %d', $process->getExitCode() ?? -1);
            if ($attempt < $this->maxAttempts) {
                $this->backoff($attempt);
            }
        }
        throw new \RuntimeException(sprintf(
            'gh call failed after %d attempts (args=%s): %s',
            $this->maxAttempts,
            implode(' ', $args),
            $lastError,
        ));
    }

    /**
     * Test seam. Subclasses can override to inject a stubbed Process that
     * doesn't actually exec a subprocess, enabling deterministic retry-
     * loop tests without a real `gh` binary on PATH.
     *
     * @param list<string> $command
     */
    protected function createProcess(array $command): Process
    {
        return new Process($command);
    }

    /**
     * Test seam. Subclasses can override to skip real sleep between
     * retry attempts (test runtimes should be sub-second, not
     * (attempts-1)! seconds).
     */
    protected function backoff(int $attempt): void
    {
        sleep($attempt);
    }
}
