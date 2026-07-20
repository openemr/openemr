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
        $process = new Process([
            'gh', 'api',
            "/search/issues?q=milestone:{$milestone}+repo:{$this->repo}+state:open",
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
            "/search/issues?q=milestone:{$milestone}+repo:{$this->repo}+state:open",
            '--jq', '.items[] | "  - #\(.number) \(.title)"',
        ]);
        $detail->mustRun();

        $output->writeln("<error>✗</error> Milestone {$milestone}: {$count} open item(s)");
        $output->write($detail->getOutput());
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
            $output->writeln("  - [{$severity}] {$summary}");
        }
        return 1;
    }
}
