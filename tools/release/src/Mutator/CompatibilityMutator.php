<?php

/**
 * Inject the "### Minimum supported versions" section into the target
 * version's `## [X.Y.Z]` block in CHANGELOG.md. The section is derived
 * from the rel branch's `ci/` matrix via `CompatibilityDeriver`
 * (mirrors ci/parse_docker_dir.sh) and rendered via
 * `CompatibilityNotesRenderer`.
 *
 * Runs AFTER `ChangelogMutator` on both scopes so the target-version
 * heading exists when this mutator injects into it. The order is
 * enforced by `ReleasePrepCommand::appendOptionalReleaseMutators()`.
 *
 * CI-directory sourcing
 *   On both rel and master scopes the mutator materializes the rel
 *   branch's `ci/` compose files into a temp dir via `git ls-tree` +
 *   `git show` (per-file blob reads). Master scope needs this because
 *   master's `ci/` can diverge from rel-820's during the release window.
 *   Rel scope also uses it (rather than reading `$projectDir/ci/`
 *   directly) so the two paths behave identically -- one code path,
 *   tested once. `git archive` was considered but `.gitattributes` has
 *   `ci/ export-ignore` (correctly, since ci configs shouldn't ship in
 *   release tarballs), which silently produces an empty archive.
 *
 * Idempotence
 *   Rerun with the same target version + rel branch produces no diff.
 *   `CompatibilityNotesRenderer::inject()` was fixed alongside this
 *   mutator to strip an existing Minimum-supported-versions block
 *   before injecting the new one (rather than duplicating).
 *
 * External-tool dependence
 *   Shells out to `git ls-tree` + `git show` via Symfony Process. Both
 *   are always available with git. No network calls (unlike
 *   ChangelogMutator's gh api dependency).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use OpenEMR\Release\CompatibilityDeriver;
use OpenEMR\Release\CompatibilityNotesRenderer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final readonly class CompatibilityMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'CHANGELOG.md';
    private const CI_RELATIVE_PATH = 'ci';
    private const REPO = 'openemr/openemr';

    public function __construct(
        private ?CompatibilityDeriver $deriver = null,
        private ?CompatibilityNotesRenderer $renderer = null,
    ) {
    }

    public function name(): string
    {
        return 'CHANGELOG.md (inject Minimum supported versions section)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        if ($context->relBranch === null) {
            throw new \RuntimeException(
                'CompatibilityMutator: relBranch is required; the release-prep'
                . ' workflow passes --rel-branch on both rel and master scope.',
            );
        }

        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $existing = file_get_contents($path);
        if ($existing === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        [$ciDir, $cleanup] = $this->materializeCiDir($context);

        try {
            $deriver = $this->deriver ?? new CompatibilityDeriver($ciDir);
            $minimums = $deriver->derive();

            $matrixUrl = sprintf(
                'https://github.com/%s/tree/%s/%s',
                self::REPO,
                $context->relBranch,
                self::CI_RELATIVE_PATH,
            );
            $renderer = $this->renderer ?? new CompatibilityNotesRenderer();
            $section = $renderer->render($minimums, $matrixUrl);
            $updated = $renderer->inject($existing, $section);

            if ($updated === $existing) {
                return MutatorResult::noop();
            }

            if (file_put_contents($path, $updated) === false) {
                throw new \RuntimeException('Cannot write ' . $path);
            }

            return new MutatorResult(
                [self::RELATIVE_PATH],
                [sprintf(
                    'CHANGELOG.md: injected Minimum supported versions from %s/ci (%s)',
                    $context->relBranch,
                    implode(', ', array_map(
                        static fn (string $k, string $v): string => "{$k}={$v}",
                        array_keys($minimums),
                        array_values($minimums),
                    )),
                )],
            );
        } finally {
            $cleanup();
        }
    }

    /**
     * Materialize rel branch's docker-compose.yml files under `ci/`
     * into a temp dir via `git ls-tree` + `git show` per file.
     * CompatibilityDeriver iterates ci-subdirs and reads each subdir's
     * docker-compose.yml, so only compose files need materializing
     * (other files in ci/ -- READMEs, dockerfiles -- are unused).
     *
     * Uses per-blob reads rather than `git archive` because
     * `.gitattributes` marks `ci/` as export-ignore (correctly, since
     * ci configs shouldn't ship in release tarballs); `git archive`
     * silently produces an empty archive.
     *
     * @return array{string, callable(): void} Tuple of the extracted
     *                                          ci dir path + a cleanup
     *                                          callable that removes the
     *                                          temp dir.
     */
    private function materializeCiDir(MutatorContext $context): array
    {
        $tempDir = sys_get_temp_dir() . '/openemr-compat-mut-' . bin2hex(random_bytes(6));
        if (!mkdir($tempDir . '/' . self::CI_RELATIVE_PATH, 0700, true)) {
            throw new \RuntimeException("Cannot create temp dir: {$tempDir}");
        }
        $cleanup = static function () use ($tempDir): void {
            (new Filesystem())->remove($tempDir);
        };

        try {
            $ls = new Process(
                [
                    'git', 'ls-tree', '-r', '--name-only',
                    (string) $context->relBranch, '--',
                    self::CI_RELATIVE_PATH . '/',
                ],
                $context->projectDir,
            );
            $ls->mustRun();

            foreach (explode("\n", trim($ls->getOutput())) as $file) {
                if (!str_ends_with($file, '/docker-compose.yml')) {
                    continue;
                }
                $show = new Process(
                    ['git', 'show', $context->relBranch . ':' . $file],
                    $context->projectDir,
                );
                $show->mustRun();

                $destPath = $tempDir . '/' . $file;
                $destDir = dirname($destPath);
                if (!is_dir($destDir) && !mkdir($destDir, 0700, true)) {
                    throw new \RuntimeException("Cannot create dir: {$destDir}");
                }
                if (file_put_contents($destPath, $show->getOutput()) === false) {
                    throw new \RuntimeException("Cannot write: {$destPath}");
                }
            }
        } catch (\Throwable $e) {
            $cleanup();
            throw $e;
        }

        return [$tempDir . '/' . self::CI_RELATIVE_PATH, $cleanup];
    }
}
