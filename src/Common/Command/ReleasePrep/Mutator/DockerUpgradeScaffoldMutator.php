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
 * (2) Create a `docker/release/upgrade/fsupgrade-(N+1).sh` stub by reading
 *     `fsupgrade-N.sh` and stripping its body down to a TODO marker.
 *     Per-release work fills in the actual upgrade logic before each ship;
 *     branch-cut only stages the file structure.
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
        return 'docker upgrade scaffold (docker-version bump + fsupgrade stub + Dockerfile manifest)';
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

        // Idempotency: if the current docker-version's fsupgrade stub
        // already records THIS cut's prior version, we already ran for
        // this cut (possibly on a previous retry). No-op cleanly.
        //
        // The signal: at rel-820 cut (target 8.2.0), the new stub we'd
        // write contains `priorOpenemrVersion="8.1.0"`. After a
        // successful run, docker-version is the new N (=12) and
        // fsupgrade-12.sh exists carrying that 8.1.0 marker. A
        // *different* cut would see a different prior version pinned
        // (e.g. fsupgrade-12.sh carrying 8.0.0 from an older cut), in
        // which case we'd still want to advance.
        $currentStubAbs = $projectDir . '/' . self::UPGRADE_DIR . '/fsupgrade-' . $current . '.sh';
        if (is_file($currentStubAbs)) {
            $stub = (string) file_get_contents($currentStubAbs);
            if (str_contains($stub, 'priorOpenemrVersion="' . $priorOpenemrVersion . '"')
                && str_contains($stub, 'Upgrade number ' . $current . ' for OpenEMR docker')
                && str_contains($stub, 'TODO: fill in upgrade logic per-release')) {
                // This is OUR stub from a prior run of this same cut.
                return MutatorResult::noop();
            }
        }

        $nextStubRelPath = self::UPGRADE_DIR . '/fsupgrade-' . $next . '.sh';
        $nextStubAbs = $projectDir . '/' . $nextStubRelPath;

        $changedFiles = [];

        // (1) Bump the three docker-version files.
        foreach (self::DOCKER_VERSION_PATHS as $relPath) {
            $abs = $projectDir . '/' . $relPath;
            if (file_put_contents($abs, $next . "\n") === false) {
                throw new \RuntimeException('Cannot write ' . $abs);
            }
            $changedFiles[] = $relPath;
        }

        // (2) Create the next fsupgrade-(N+1).sh stub.
        $stubContents = $this->renderStub($next, $priorOpenemrVersion);
        if (file_put_contents($nextStubAbs, $stubContents) === false) {
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
     * The fsupgrade-(N+1).sh's `priorOpenemrVersion` is the X.Y.Z of the
     * line that's shipping at the prior docker-version.
     *
     * Branch-cut (no fromVersion): for a cut of rel-820 (target 8.2.0),
     * the prior shipped line was 8.1.0; encoded as "X.(target_minor-1).0".
     *
     * Patch-prep (fromVersion supplied): for a rel-810 patch-prep to
     * 8.1.1, the prior shipped version was 8.1.0 — use the explicit
     * from-version directly instead of decrementing the minor.
     *
     * Per-release work may refine this when filling in the upgrade body;
     * the scaffold's job is structural consistency.
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

    private function renderStub(int $nextVersion, string $priorOpenemrVersion): string
    {
        return <<<BASH
        #!/bin/bash
        # Upgrade number {$nextVersion} for OpenEMR docker
        #  From prior version {$priorOpenemrVersion} (needed for the sql upgrade script).
        priorOpenemrVersion="{$priorOpenemrVersion}"
        echo "Start: Upgrade to docker-version {$nextVersion}"
        # TODO: fill in upgrade logic per-release; see prior fsupgrade-*.sh for examples
        echo "Completed: Upgrade to docker-version {$nextVersion}"

        BASH;
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
