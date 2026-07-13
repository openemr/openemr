<?php

/**
 * Wrapper around the gh CLI for GitHub API calls.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Process\Process;

class GitHubApi
{
    public function __construct(
        private readonly string $repo = 'openemr/openemr',
    ) {
    }

    /**
     * Call a GitHub API endpoint via gh CLI and return decoded JSON.
     *
     * @return list<array<string, mixed>>
     */
    public function paginate(string $endpoint): array
    {
        $process = new Process([
            'gh', 'api', '--paginate', '--slurp',
            "/repos/{$this->repo}{$endpoint}",
        ]);
        $process->mustRun();

        $pages = json_decode($process->getOutput(), true);
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
            $process = new Process([
                'gh', 'api',
                "{$endpoint}?per_page=250&page={$page}",
                '--jq', '.commits[].sha',
            ]);
            $process->mustRun();

            $output = trim($process->getOutput());
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
     * Resolve commit SHAs to their associated pull requests.
     *
     * Deduplicates by PR number. `author` is the PR user's login (e.g.
     * `dependabot[bot]`), needed by ChangelogGenerator's noise filter to
     * distinguish dependabot/release-bot PRs.
     *
     * @param list<string> $shas
     * @return list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}>
     */
    public function prsForCommits(array $shas): array
    {
        /** @var array<int, array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}> $seen */
        $seen = [];

        foreach ($shas as $sha) {
            $process = new Process([
                'gh', 'api',
                "/repos/{$this->repo}/commits/{$sha}/pulls",
                '--jq', '[.[] | {number, title, labels: [.labels[] | {name}], url: .html_url, author: .user.login}]',
            ]);
            $process->mustRun();

            /** @var list<array{number: int, title: string, labels: list<array{name: string}>, url: string, author: string}> $prs */
            $prs = json_decode($process->getOutput(), true) ?? [];
            foreach ($prs as $pr) {
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
     * @return list<array<string, mixed>>
     */
    public function publishedAdvisories(): array
    {
        return $this->paginate('/security-advisories?state=published&per_page=100');
    }
}
