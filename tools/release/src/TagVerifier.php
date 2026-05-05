<?php

/**
 * Verify a release tag is annotated and its message conforms to the
 * openemr/openemr-devops#664 spec (contains a version, ISO date, and merge SHA).
 *
 * Vendored from openemr/openemr-devops; see
 * tools/release/bin/check-vendored.php in that repo for the drift check.
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

final readonly class TagVerifier
{
    public function __construct(
        private string $repoDir,
    ) {
    }

    public function verify(string $tagName): TagVerificationResult
    {
        $errors = [];
        $type = $this->git(['cat-file', '-t', $tagName]);
        $isAnnotated = $type === 'tag';
        if (!$isAnnotated) {
            $errors[] = sprintf(
                '%s is not annotated (object type: %s); the spec requires `git tag -a`',
                $tagName,
                $type,
            );
            return new TagVerificationResult($tagName, false, null, null, null, $errors);
        }

        $message = $this->extractMessage($this->git(['cat-file', '-p', $tagName]));

        $version = $this->firstMatch('/\b(\d+\.\d+\.\d+)\b/', $message);
        if ($version === null) {
            $errors[] = 'tag message does not contain a version (MAJOR.MINOR.PATCH)';
        }

        $date = $this->firstMatch('/\b(\d{4}-\d{2}-\d{2})\b/', $message);
        if ($date === null) {
            $errors[] = 'tag message does not contain an ISO date (YYYY-MM-DD)';
        }

        $mergeSha = $this->firstMatch('/\b([0-9a-f]{40})\b/', $message);
        if ($mergeSha === null) {
            $errors[] = 'tag message does not contain a merge-commit SHA (40 hex chars)';
        }

        return new TagVerificationResult($tagName, true, $version, $date, $mergeSha, $errors);
    }

    /**
     * @param list<string> $args
     */
    private function git(array $args): string
    {
        $process = new Process(['git', ...$args], $this->repoDir);
        $process->mustRun();
        return rtrim($process->getOutput(), "\n");
    }

    /**
     * Extract the message body of a `git cat-file -p <tag>` annotated-tag dump.
     *
     * Headers (object/type/tag/tagger) precede a blank line, then the message.
     */
    private function extractMessage(string $catFileOutput): string
    {
        $parts = preg_split('/\R\R/', $catFileOutput, 2);
        if ($parts === false || count($parts) < 2) {
            return '';
        }
        return $parts[1];
    }

    private function firstMatch(string $pattern, string $subject): ?string
    {
        if (preg_match($pattern, $subject, $matches) !== 1) {
            return null;
        }
        return $matches[1];
    }
}
