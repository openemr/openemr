<?php

/**
 * Build the release-notes JSON manifest the conductor passes to the
 * openemr:release-prep ChangelogMutator. Combines a milestone lookup
 * with a `gh pr list --state merged --search milestone:<version>`
 * enumeration, categorises each PR, and emits the JSON shape
 * Manifest::fromJsonFile expects.
 *
 * Categorisation is deliberately conservative:
 *   - PRs labelled `Security` → Security
 *   - PR title starts with `fix(...)` / `fix:` → Bug Fixes
 *   - PR title starts with `feat(...)` / `feat:` → Added
 *   - Everything else → Changed (catch-all)
 * OpenEMR's label taxonomy is scope-based (Calendar, Authentication,
 * etc.) rather than type-based, so the Conventional Commits prefix in
 * the PR title is the primary signal.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Process\Process;

/**
 * @phpstan-type GhPr array{number: int, title: string, url: string, labels: list<array{name: string}>}
 * @phpstan-type GhMilestone array{number: int, title: string, html_url: string}
 * @phpstan-type ProcessRunner callable(Process): string
 */
final readonly class ReleaseNotesCollector
{
    /**
     * @param ProcessRunner|null $processRunner Override only in tests.
     */
    public function __construct(
        private string $repo,
        private mixed $processRunner = null,
    ) {
    }

    /**
     * @return array{
     *   version: string,
     *   milestone: array{number: int, url: string},
     *   date: string,
     *   sections: array<string, list<array{title: string, number: int, url: string}>>,
     * }
     */
    public function collect(string $version, string $date): array
    {
        $milestone = $this->lookupMilestone($version);
        if ($milestone === null) {
            throw new \RuntimeException(sprintf(
                'No milestone titled "%s" found on %s; create the milestone before tagging',
                $version,
                $this->repo,
            ));
        }

        $prs = $this->listMergedPrs($version);
        $sections = $this->categorise($prs);

        return [
            'version' => $version,
            'milestone' => [
                'number' => $milestone['number'],
                // Match the convention of prior CHANGELOG entries (e.g. 8.0.0.3),
                // which link to the milestone's closed-issues view.
                'url' => $milestone['html_url'] . '?closed=1',
            ],
            'date' => $date,
            'sections' => $sections,
        ];
    }

    /**
     * @return GhMilestone|null
     */
    private function lookupMilestone(string $version): ?array
    {
        $stdout = $this->runGh([
            'gh', 'api', '--paginate',
            sprintf('repos/%s/milestones?state=all&per_page=100', $this->repo),
            '--jq', sprintf('.[] | select(.title == "%s")', $version),
        ]);
        $stdout = trim($stdout);
        if ($stdout === '') {
            return null;
        }
        // --jq emits one matching object per line; take the first.
        $firstLine = explode("\n", $stdout, 2)[0];
        $decoded = json_decode($firstLine, true, 16, JSON_THROW_ON_ERROR);
        if (
            !is_array($decoded)
            || !is_int($decoded['number'] ?? null)
            || !is_string($decoded['title'] ?? null)
            || !is_string($decoded['html_url'] ?? null)
        ) {
            throw new \RuntimeException('Malformed milestone payload from gh api');
        }
        return [
            'number' => $decoded['number'],
            'title' => $decoded['title'],
            'html_url' => $decoded['html_url'],
        ];
    }

    /**
     * @return list<GhPr>
     */
    private function listMergedPrs(string $version): array
    {
        $stdout = $this->runGh([
            'gh', 'pr', 'list',
            '--repo', $this->repo,
            '--state', 'merged',
            '--search', sprintf('milestone:%s', $version),
            '--limit', '500',
            '--json', 'number,title,url,labels',
        ]);
        $stdout = trim($stdout);
        if ($stdout === '') {
            return [];
        }
        $decoded = json_decode($stdout, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('gh pr list did not return a JSON array');
        }
        $rows = [];
        foreach ($decoded as $row) {
            if (
                !is_array($row)
                || !is_int($row['number'] ?? null)
                || !is_string($row['title'] ?? null)
                || !is_string($row['url'] ?? null)
                || !is_array($row['labels'] ?? null)
            ) {
                throw new \RuntimeException('gh pr list returned a malformed row');
            }
            $labels = [];
            foreach ($row['labels'] as $label) {
                if (!is_array($label) || !is_string($label['name'] ?? null)) {
                    throw new \RuntimeException('gh pr list returned a malformed label');
                }
                $labels[] = ['name' => $label['name']];
            }
            $rows[] = [
                'number' => $row['number'],
                'title' => $row['title'],
                'url' => $row['url'],
                'labels' => $labels,
            ];
        }
        return $rows;
    }

    /**
     * @param list<GhPr> $prs
     * @return array<string, list<array{title: string, number: int, url: string}>>
     */
    private function categorise(array $prs): array
    {
        $buckets = [
            'security' => [],
            'bug_fixes' => [],
            'added' => [],
            'changed' => [],
        ];
        foreach ($prs as $pr) {
            $section = $this->classify($pr);
            $buckets[$section][] = [
                'title' => $this->stripConventionalPrefix($pr['title']),
                'number' => $pr['number'],
                'url' => $pr['url'],
            ];
        }
        return $buckets;
    }

    /**
     * @param GhPr $pr
     */
    private function classify(array $pr): string
    {
        foreach ($pr['labels'] as $label) {
            if (strcasecmp($label['name'], 'Security') === 0) {
                return 'security';
            }
        }
        if (preg_match('/^feat(?:\([^)]*\))?!?:/i', $pr['title']) === 1) {
            return 'added';
        }
        if (preg_match('/^fix(?:\([^)]*\))?!?:/i', $pr['title']) === 1) {
            return 'bug_fixes';
        }
        return 'changed';
    }

    private function stripConventionalPrefix(string $title): string
    {
        $stripped = preg_replace(
            '/^(?:feat|fix|chore|docs|style|refactor|perf|test|build|ci|revert)(?:\([^)]*\))?!?:\s*/i',
            '',
            $title,
        );
        return $stripped ?? $title;
    }

    /**
     * @param list<string> $command
     */
    private function runGh(array $command): string
    {
        $process = new Process($command);
        $process->setTimeout(120.0);
        if ($this->processRunner !== null) {
            $runner = $this->processRunner;
            return $runner($process);
        }
        $process->mustRun();
        return $process->getOutput();
    }
}
