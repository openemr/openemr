<?php

/**
 * Scaffolds the docker-upgrade machinery for a new rel-branch cut. Runs
 * on BOTH rel-side and master-side of the branch-cut workflow; the result
 * is byte-identical between the two sides (cross-branch sync requirement
 * per the `docker upgrade actions mandatory per release` rule + PRs
 * #12608 / #12609).
 *
 * Three transformations, all idempotent:
 *
 * (1) Bump the three docker-version files (`docker-version`,
 *     `docker/release/upgrade/docker-version`, `sites/default/docker-version`)
 *     from N to N+1. All three must already agree on N or the mutator
 *     refuses to run (drift between them is a separate bug to surface).
 *
 * (2) Create a `docker/release/upgrade/fsupgrade-(N+1).sh` by copying
 *     `fsupgrade-N.sh` in full and applying five line-level substitutions
 *     (the docker-version number in the header comment and echoes, the
 *     prior-openemr-version marker in the comment + shell variable). The
 *     actual upgrade body is preserved byte-for-byte from the prior file
 *     — it already carries a valid working upgrade against the prior
 *     shipped version, and per-release work refines it in-place as needed.
 *     Copying rather than stubbing preserves the shellcheck directives,
 *     trailing newline, and existing upgrade logic so the resulting file
 *     is immediately shellcheck-clean and runnable.
 *
 * (3) Update `docker/release/Dockerfile` to add `fsupgrade-(N+1).sh` to
 *     BOTH the `COPY upgrade/...` block AND the `RUN chmod 500 ...` block.
 *
 * Idempotency signal: if `fsupgrade-(N+1).sh` already exists, all three
 * transformations are assumed done and the mutator no-ops. (The
 * docker-version files are still validated for drift.)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;

final readonly class DockerUpgradeScaffoldMutator implements MutatorInterface
{
    private const DOCKER_VERSION_PATHS = [
        'docker-version',
        'docker/release/upgrade/docker-version',
        'sites/default/docker-version',
    ];
    private const UPGRADE_DIR = 'docker/release/upgrade';
    private const DOCKERFILE = 'docker/release/Dockerfile';

    public function name(): string
    {
        return 'docker upgrade scaffold (docker-version bump + fsupgrade copy-from-prior + Dockerfile manifest)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $projectDir = $context->projectDir;

        // Read all three docker-version files and confirm they agree.
        $currentVersions = [];
        foreach (self::DOCKER_VERSION_PATHS as $relPath) {
            $abs = $projectDir . '/' . $relPath;
            $raw = file_get_contents($abs);
            if ($raw === false) {
                throw new \RuntimeException('Cannot read ' . $abs);
            }
            $trimmed = trim($raw);
            if (preg_match('/^\d+$/', $trimmed) !== 1) {
                throw new \RuntimeException(
                    'docker-version file does not contain a bare integer: ' . $abs . ' (got: ' . $trimmed . ')',
                );
            }
            $currentVersions[$relPath] = (int) $trimmed;
        }
        $unique = array_unique($currentVersions);
        if (count($unique) !== 1) {
            throw new \RuntimeException(
                'docker-version files disagree across the three locations: '
                . json_encode($currentVersions, JSON_THROW_ON_ERROR)
                . ' — resolve drift manually before re-running.',
            );
        }
        $current = reset($unique);
        $next = $current + 1;

        $priorOpenemrVersion = $this->derivePriorOpenemrVersion($context);
        $targetVersion = $context->versionString();

        // Idempotency: if the current docker-version's fsupgrade file
        // already records THIS cut's target version + prior version, we
        // already ran for this cut (possibly on a previous retry). No-op
        // cleanly.
        //
        // The signal: at rel-820 cut (target 8.2.0), the new file we'd
        // write contains `priorOpenemrVersion="8.2.0"` (target-version) —
        // i.e., the prior line the NEXT cut's fsupgrade will need to
        // upgrade from. After a successful run, docker-version is the
        // new N (=12) and fsupgrade-12.sh exists carrying that 8.2.0
        // marker. A *different* cut would see a different target-version
        // pinned (e.g. fsupgrade-12.sh carrying 8.0.0 from an older cut),
        // in which case we'd still want to advance.
        $currentStubAbs = $projectDir . '/' . self::UPGRADE_DIR . '/fsupgrade-' . $current . '.sh';
        if (is_file($currentStubAbs)) {
            $existing = (string) file_get_contents($currentStubAbs);
            if (str_contains($existing, 'priorOpenemrVersion="' . $targetVersion . '"')
                && str_contains($existing, 'Upgrade number ' . $current . ' for OpenEMR docker')) {
                // This is OUR file from a prior run of this same cut.
                return MutatorResult::noop();
            }
        }

        $nextStubRelPath = self::UPGRADE_DIR . '/fsupgrade-' . $next . '.sh';
        $nextStubAbs = $projectDir . '/' . $nextStubRelPath;
        $priorStubRelPath = self::UPGRADE_DIR . '/fsupgrade-' . $current . '.sh';
        $priorStubAbs = $projectDir . '/' . $priorStubRelPath;

        $changedFiles = [];

        // (1) Bump the three docker-version files.
        foreach (self::DOCKER_VERSION_PATHS as $relPath) {
            $abs = $projectDir . '/' . $relPath;
            if (file_put_contents($abs, $next . "\n") === false) {
                throw new \RuntimeException('Cannot write ' . $abs);
            }
            $changedFiles[] = $relPath;
        }

        // (2) Create the next fsupgrade-(N+1).sh by copying the prior
        // file in full and applying the five line-level substitutions.
        $priorContents = file_get_contents($priorStubAbs);
        if ($priorContents === false) {
            throw new \RuntimeException('Cannot read ' . $priorStubAbs);
        }
        $nextContents = $this->deriveNextFromPrior(
            $priorContents,
            $current,
            $next,
            $priorOpenemrVersion,
            $targetVersion,
            $priorStubAbs,
        );
        if (file_put_contents($nextStubAbs, $nextContents) === false) {
            throw new \RuntimeException('Cannot write ' . $nextStubAbs);
        }
        $changedFiles[] = $nextStubRelPath;

        // (3) Update Dockerfile: extend COPY block + RUN chmod block.
        $dockerfileAbs = $projectDir . '/' . self::DOCKERFILE;
        $dockerfile = file_get_contents($dockerfileAbs);
        if ($dockerfile === false) {
            throw new \RuntimeException('Cannot read ' . $dockerfileAbs);
        }
        $updated = $this->extendCopyBlock($dockerfile, $current, $next);
        $updated = $this->extendChmodBlock($updated, $current, $next);
        if ($updated !== $dockerfile) {
            if (file_put_contents($dockerfileAbs, $updated) === false) {
                throw new \RuntimeException('Cannot write ' . $dockerfileAbs);
            }
            $changedFiles[] = self::DOCKERFILE;
        }

        return new MutatorResult($changedFiles);
    }

    /**
     * The fsupgrade-N.sh's existing `priorOpenemrVersion` marker
     * (whatever value the prior file records — the version the current
     * fsupgrade-N.sh upgrades FROM). The next fsupgrade-(N+1).sh, by
     * contrast, will upgrade from the version we're SHIPPING with this
     * cut (i.e. the target-version), so the substitution's replacement
     * value is the target-version.
     *
     * Branch-cut (no fromVersion): for a cut of rel-820 (target 8.2.0),
     * fsupgrade-11.sh's prior marker is 8.1.0 (rel-810's shipped version),
     * and fsupgrade-12.sh's prior marker becomes 8.2.0.
     *
     * Patch-prep (fromVersion supplied): the from-version is the
     * prior-patch shipped version, e.g. 8.1.0 for a rel-810 8.1.1
     * patch-prep — the same shape as the branch-cut case.
     */
    private function derivePriorOpenemrVersion(MutatorContext $context): string
    {
        if ($context->fromVersion !== null) {
            return $context->fromVersion;
        }
        $priorMinor = $context->minor - 1;
        if ($priorMinor < 0) {
            $priorMinor = 0;
        }
        return sprintf('%d.%d.0', $context->major, $priorMinor);
    }

    /**
     * Copy the prior fsupgrade-N.sh in full and apply exactly five
     * line-level substitutions to produce fsupgrade-(N+1).sh. All other
     * content (shellcheck directives, blank lines, upgrade body, trailing
     * newline) is preserved byte-for-byte.
     *
     * If any of the five expected anchor patterns is missing from the
     * prior file, we throw rather than silently producing a corrupt
     * output — the prior file's shape is a load-bearing schema.
     */
    private function deriveNextFromPrior(
        string $priorContents,
        int $current,
        int $next,
        string $priorOpenemrVersion,
        string $targetVersion,
        string $priorFilePath,
    ): string {
        $substitutions = [
            [
                '# Upgrade number ' . $current . ' for OpenEMR docker',
                '# Upgrade number ' . $next . ' for OpenEMR docker',
            ],
            [
                '#  From prior version ' . $priorOpenemrVersion . ' (needed for the sql upgrade script).',
                '#  From prior version ' . $targetVersion . ' (needed for the sql upgrade script).',
            ],
            [
                'priorOpenemrVersion="' . $priorOpenemrVersion . '"',
                'priorOpenemrVersion="' . $targetVersion . '"',
            ],
            [
                'echo "Start: Upgrade to docker-version ' . $current . '"',
                'echo "Start: Upgrade to docker-version ' . $next . '"',
            ],
            [
                'echo "Completed: Upgrade to docker-version ' . $current . '"',
                'echo "Completed: Upgrade to docker-version ' . $next . '"',
            ],
        ];

        $output = $priorContents;
        foreach ($substitutions as [$needle, $replacement]) {
            if (!str_contains($output, $needle)) {
                throw new \RuntimeException(sprintf(
                    'fsupgrade scaffold expected to substitute %s in %s but the anchor was not found; refusing to write a corrupt fsupgrade-%d.sh',
                    var_export($needle, true),
                    $priorFilePath,
                    $next,
                ));
            }
            $output = str_replace($needle, $replacement, $output);
        }
        return $output;
    }

    /**
     * Append `upgrade/fsupgrade-(N+1).sh \` to the COPY block that ends
     * with `/root/`. Anchored on the existing `upgrade/fsupgrade-N.sh \`
     * line to keep the surgical edit narrow.
     */
    private function extendCopyBlock(string $dockerfile, int $current, int $next): string
    {
        $needle = '     upgrade/fsupgrade-' . $current . '.sh \\';
        $replacement = $needle . "\n" . '     upgrade/fsupgrade-' . $next . '.sh \\';
        if (!str_contains($dockerfile, $needle)) {
            throw new \RuntimeException(
                'Dockerfile COPY block does not contain expected anchor line: ' . $needle,
            );
        }
        // Already added on a prior pass? (defensive — should not happen
        // because of the fsupgrade-(N+1).sh idempotency gate above, but
        // cheap to verify.)
        $alreadyAdded = '     upgrade/fsupgrade-' . $next . '.sh \\';
        if (str_contains($dockerfile, $alreadyAdded)) {
            return $dockerfile;
        }
        return str_replace($needle, $replacement, $dockerfile);
    }

    /**
     * Append `/root/fsupgrade-(N+1).sh` to the RUN chmod block. Anchored
     * on the existing `/root/fsupgrade-N.sh` final line (no trailing
     * backslash on the current last entry).
     */
    private function extendChmodBlock(string $dockerfile, int $current, int $next): string
    {
        // The last fsupgrade entry in the chmod block has no trailing
        // backslash. Match: spaces + `/root/fsupgrade-{current}.sh` + end-of-line.
        $pattern = '/^(\s+)\/root\/fsupgrade-' . $current . '\.sh$/m';
        if (preg_match($pattern, $dockerfile, $m) !== 1) {
            throw new \RuntimeException(
                'Dockerfile RUN chmod block does not contain expected anchor line: '
                . '/root/fsupgrade-' . $current . '.sh',
            );
        }
        // Idempotency: bail out if the next line already exists.
        if (preg_match('/^\s+\/root\/fsupgrade-' . $next . '\.sh$/m', $dockerfile) === 1) {
            return $dockerfile;
        }
        $indent = $m[1];
        $newBlock = $indent . '/root/fsupgrade-' . $current . '.sh \\'
            . "\n" . $indent . '/root/fsupgrade-' . $next . '.sh';
        $result = preg_replace($pattern, $newBlock, $dockerfile, 1);
        return $result ?? $dockerfile;
    }
}
