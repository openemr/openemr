<?php

/**
 * Lists files to scan from git, so untracked and ignored files never count
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

use Symfony\Component\Process\Process;

final readonly class GitFileLister
{
    public function __construct(
        private string $projectDir,
    ) {
    }

    /**
     * @return list<string>
     */
    public function tracked(): array
    {
        return $this->paths(['git', 'ls-files', '-z']);
    }

    /**
     * Files added, copied, modified, or renamed since the merge base of `$ref`
     * and HEAD, as a pull request diff shows them. Deleted files are left out.
     *
     * @return list<string>
     */
    public function changedSince(string $ref): array
    {
        return $this->paths(['git', 'diff', '--name-only', '-z', '--diff-filter=ACMR', '--end-of-options', $ref . '...HEAD']);
    }

    /**
     * @param list<string> $command
     * @return list<string>
     */
    private function paths(array $command): array
    {
        $process = new Process($command, $this->projectDir);
        $process->mustRun();
        return array_values(array_filter(
            explode(chr(0), $process->getOutput()),
            static fn(string $path): bool => $path !== '',
        ));
    }
}
