<?php

/**
 * Sweep the project for `docker-version` files (each a single-integer
 * file used by the Docker entrypoint to detect upgrades) and increment
 * each by one. Sweeps so new docker-version files added later are picked
 * up automatically without editing this mutator.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;

final readonly class DockerVersionFileMutator implements MutatorInterface
{
    private const EXCLUDED_DIR_NAMES = ['vendor', 'node_modules', '.git'];

    public function name(): string
    {
        return 'docker-version files (increment)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $changed = [];
        $messages = [];
        foreach ($this->findDockerVersionFiles($context->projectDir) as $absolutePath) {
            $contents = file_get_contents($absolutePath);
            if ($contents === false) {
                throw new \RuntimeException('Cannot read ' . $absolutePath);
            }
            $trimmed = trim($contents);
            if (preg_match('/^\d+$/', $trimmed) !== 1) {
                $messages[] = sprintf(
                    'skipped %s: not a single integer (contents: %s)',
                    $this->relativize($absolutePath, $context->projectDir),
                    $trimmed,
                );
                continue;
            }
            $current = (int) $trimmed;
            $next = $current + 1;
            $needsTrailingNewline = str_ends_with($contents, "\n");
            $newContents = (string) $next . ($needsTrailingNewline ? "\n" : '');
            if ($newContents === $contents) {
                continue;
            }
            // Idempotence: the conductor stamps the per-release bump
            // exactly once per push. Re-running on the same push window
            // would double-bump, which is wrong. We rely on the
            // workflow to invoke this mutator at most once per push;
            // re-running after a push is a separate push and a separate
            // intended bump.
            if (file_put_contents($absolutePath, $newContents) === false) {
                throw new \RuntimeException('Cannot write ' . $absolutePath);
            }
            // Validate post-write: the file should be the expected
            // bumped integer with the original trailing-newline shape.
            // The Docker entrypoint reads this as an integer so a
            // non-integer write here would silently break upgrades.
            $writtenInt = (int) trim($newContents);
            if ($writtenInt !== $next || preg_match('/^\d+\n?$/', $newContents) !== 1) {
                throw new \RuntimeException(sprintf(
                    'docker-version write produced %s at %s; expected integer %d',
                    var_export($newContents, true),
                    $absolutePath,
                    $next,
                ));
            }
            $changed[] = $this->relativize($absolutePath, $context->projectDir);
        }
        return new MutatorResult($changed, $messages);
    }

    /**
     * @return list<string>
     */
    private function findDockerVersionFiles(string $projectDir): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($projectDir, \FilesystemIterator::SKIP_DOTS),
                function (\SplFileInfo $entry): bool {
                    if ($entry->isDir()) {
                        return !in_array($entry->getFilename(), self::EXCLUDED_DIR_NAMES, true);
                    }
                    return $entry->getFilename() === 'docker-version';
                },
            ),
        );
        $matches = [];
        /** @var \SplFileInfo $entry */
        foreach ($iterator as $entry) {
            if ($entry->isFile() && $entry->getFilename() === 'docker-version') {
                $matches[] = $entry->getPathname();
            }
        }
        sort($matches);
        return $matches;
    }

    private function relativize(string $absolutePath, string $projectDir): string
    {
        $prefix = rtrim($projectDir, '/') . '/';
        return str_starts_with($absolutePath, $prefix)
            ? substr($absolutePath, strlen($prefix))
            : $absolutePath;
    }
}
