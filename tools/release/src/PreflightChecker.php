<?php

/**
 * Pre-release checks: milestone completeness and unpublished GHSAs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class PreflightChecker
{
    public function __construct(
        private readonly GitHubApi $api,
        private readonly string $repo,
    ) {
    }

    /**
     * Check that a milestone has no open issues or PRs.
     *
     * @return int 0 on success, 1 on failure
     */
    public function checkMilestone(string $milestone, OutputInterface $output): int
    {
        // URL-encode the search query: `+` as space-substitute only works
        // for single-word milestones, and a multi-word milestone
        // ("Fixed in 8.2.0") or one containing `&`/`?` would silently
        // corrupt the query. Quote the milestone value so GitHub search
        // treats it as one term.
        $query = urlencode(sprintf('milestone:"%s" repo:%s state:open', $milestone, $this->repo));

        $process = new Process([
            'gh', 'api',
            "/search/issues?q={$query}",
            '--jq', '.total_count',
        ]);
        $process->mustRun();
        $count = (int) trim($process->getOutput());

        if ($count === 0) {
            $output->writeln("<info>✓</info> Milestone {$milestone}: no open items");
            return 0;
        }

        $detail = new Process([
            'gh', 'api',
            "/search/issues?q={$query}",
            '--jq', '.items[] | "  - #\(.number) \(.title)"',
        ]);
        $detail->mustRun();

        $output->writeln("<error>✗</error> Milestone {$milestone}: {$count} open item(s)");
        // OUTPUT_RAW: issue titles are external text and can contain `<`/`>`
        // that Symfony Console would otherwise try to parse as style tags.
        $output->write($detail->getOutput(), false, OutputInterface::OUTPUT_RAW);
        return 1;
    }

    /**
     * Check for unpublished security advisories.
     *
     * @return int 0 on success, 1 on failure
     */
    public function checkGhsa(OutputInterface $output): int
    {
        $advisories = $this->api->paginate('/security-advisories');
        $unpublished = array_filter(
            $advisories,
            static fn(array $a): bool => $a['state'] !== 'published',
        );

        if (count($unpublished) === 0) {
            $output->writeln('<info>✓</info> No unpublished security advisories');
            return 0;
        }

        $output->writeln(sprintf(
            '<error>✗</error> %d unpublished security advisory/ies:',
            count($unpublished),
        ));
        foreach ($unpublished as $advisory) {
            $severity = is_string($advisory['severity'] ?? null) ? $advisory['severity'] : 'unknown';
            $summary = is_string($advisory['summary'] ?? null) ? $advisory['summary'] : '';
            // OUTPUT_RAW: GHSA summaries are external text and can contain
            // `<`/`>` chars that Symfony Console would parse as style tags.
            $output->writeln("  - [{$severity}] {$summary}", OutputInterface::OUTPUT_RAW);
        }
        return 1;
    }
}
