<?php

/**
 * Translate openemr/openemr-devops#664 release-branch and tag names to
 * MAJOR.MINOR.PATCH versions, and derive a `prev_release` for the
 * dispatch envelope by reading the latest annotated v* tag from a
 * local git checkout.
 *
 * Centralised here so the conductor workflow doesn't have to duplicate
 * the regex parsing in shell.
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

final readonly class BranchVersionResolver
{
    public function __construct(
        private string $repoDir,
    ) {
    }

    public static function branchToVersion(string $branch): string
    {
        if (preg_match('/^rel-(\d+)(\d)0$/', $branch, $m) !== 1) {
            throw new \InvalidArgumentException(
                'branch must match rel-<MAJOR><MINOR>0; got: ' . $branch,
            );
        }
        return sprintf('%s.%s.0', $m[1], $m[2]);
    }

    /**
     * Returns the latest v<MAJOR>_<MINOR>_<PATCH> tag whose version is
     * strictly less than the target, or synthesises one based on the
     * target version when no qualifying tag exists yet (initial run on
     * a fresh repo, or a target older than every tag in the repo).
     *
     * Filtering by target catches the case where a higher-numbered
     * release was tagged out-of-order: cutting 8.1.0 should not report
     * 8.2.0 as the previous release.
     */
    public function previousRelease(string $targetVersion): string
    {
        if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $targetVersion, $tm) !== 1) {
            throw new \InvalidArgumentException(
                'target version must be MAJOR.MINOR.PATCH; got: ' . $targetVersion,
            );
        }
        $latestTag = $this->latestVersionTagBelow($targetVersion);
        if ($latestTag !== null) {
            return $latestTag;
        }
        $prevMinor = max(0, ((int) $tm[2]) - 1);
        return sprintf('%s.%d.0', $tm[1], $prevMinor);
    }

    private function latestVersionTagBelow(string $targetVersion): ?string
    {
        $process = new Process(['git', 'tag', '--list', 'v*', '--sort=-v:refname'], $this->repoDir);
        $process->mustRun();
        $output = trim($process->getOutput());
        if ($output === '') {
            return null;
        }
        $split = preg_split('/\R/', $output);
        $lines = $split === false ? [] : $split;
        foreach ($lines as $tag) {
            if (preg_match('/^v(\d+)_(\d+)_(\d+)$/', $tag, $m) !== 1) {
                continue;
            }
            $candidate = sprintf('%s.%s.%s', $m[1], $m[2], $m[3]);
            if (version_compare($candidate, $targetVersion, '<')) {
                return $candidate;
            }
        }
        return null;
    }
}
