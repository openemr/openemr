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
 *     The `priorOpenemrVersion` value written into the new file is the
 *     LAST SHIPPED version from the prior release line — derived by
 *     scanning `sql/*_upgrade.sql` and taking the highest LEFT (see
 *     `derivePriorOpenemrVersion` for the full contract, including the
 *     patch-prep `fromVersion` override and the strictly-less-than-target
 *     invariant that catches ordering / regression bugs).
 *
 * (3) Update `docker/release/Dockerfile` to add `fsupgrade-(N+1).sh` to
 *     BOTH the `COPY upgrade/...` block AND the `RUN chmod 500 ...` block.
 *
 * Idempotency signal: the mutator recognises its own post-scaffold work
 * by inspecting fsupgrade-(current).sh — after a successful run,
 * docker-version has been bumped to N+1 so `current` reads as the bumped
 * value and the file at that path is one WE wrote. Two markers must
 * match: `Upgrade number <current> for OpenEMR docker` (structural) and
 * `priorOpenemrVersion="<X>"` where X is what we WOULD write now
 * (derived from the sql scan with any at-or-above-target bridge filtered
 * out, mirroring the pre-scaffold state — see
 * `derivePriorOpenemrVersionFromSqlIgnoringAtOrAboveTarget`). If either
 * marker differs, we're either at the pre-scaffold state (fsupgrade-N
 * is the prior cycle's file) or in a wrong-ordering scenario (Sql
 * accidentally ran first), and the mutation proceeds — in the
 * wrong-ordering case, `derivePriorOpenemrVersion` trips the
 * strictly-less-than-target invariant with a clear ordering-bug message.
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

        $targetVersion = $context->versionString();

        // Compute the "pre-scaffold" priorOpenemrVersion — what the
        // derivation SHOULD produce ignoring any sql bridge whose left
        // >= target. That's the value we WOULD write into a fresh
        // fsupgrade-(N+1).sh. Idempotency compares it against the
        // marker in fsupgrade-(current).sh.
        //
        // Master-side post-scaffold: the sibling SqlUpgradeSkeletonMutator
        // has run and added a `<target>-to-<next>_upgrade.sql` bridge.
        // A naive sql scan would then yield `target` and trip the
        // strictly-less-than-target invariant. Filtering out bridges
        // with left >= target restores the "pre-scaffold view", which
        // is the value we ACTUALLY wrote into fsupgrade-(current).sh
        // during the successful run. If they match, we no-op cleanly.
        //
        // Wrong-ordering scenario (Sql accidentally ran BEFORE Docker
        // in the same chain): docker-version hasn't been bumped yet,
        // fsupgrade-(current).sh is the PRIOR cycle's file with an
        // OLDER priorOpenemrVersion marker. That doesn't match the
        // pre-scaffold-view derivation, so the idempotency short-circuit
        // is skipped and derivePriorOpenemrVersion is called — which
        // then trips the invariant with the clear ordering-bug message.
        $preScaffoldPrior = $context->fromVersion
            ?? $this->derivePriorOpenemrVersionFromSqlIgnoringAtOrAboveTarget(
                $context->projectDir,
                $targetVersion,
            );
        $currentStubAbs = $projectDir . '/' . self::UPGRADE_DIR . '/fsupgrade-' . $current . '.sh';
        if ($preScaffoldPrior !== null && is_file($currentStubAbs)) {
            $existing = (string) file_get_contents($currentStubAbs);
            if (
                str_contains($existing, 'Upgrade number ' . $current . ' for OpenEMR docker')
                && str_contains($existing, 'priorOpenemrVersion="' . $preScaffoldPrior . '"')
            ) {
                return MutatorResult::noop();
            }
        }

        $priorOpenemrVersion = $this->derivePriorOpenemrVersion($context);

        $nextStubRelPath = self::UPGRADE_DIR . '/fsupgrade-' . $next . '.sh';
        $nextStubAbs = $projectDir . '/' . $nextStubRelPath;
        $priorStubRelPath = self::UPGRADE_DIR . '/fsupgrade-' . $current . '.sh';
        $priorStubAbs = $projectDir . '/' . $priorStubRelPath;

        // Validate + derive everything that can throw BEFORE any
        // destructive writes. Otherwise a mid-flight failure (unreadable
        // prior fsupgrade, missing marker, invariant violation) would
        // leave docker-version files bumped without the paired new
        // fsupgrade — a partial state that's harder to recover from
        // than a clean throw.
        $priorContents = file_get_contents($priorStubAbs);
        if ($priorContents === false) {
            throw new \RuntimeException('Cannot read ' . $priorStubAbs);
        }
        $priorFileMarker = $this->extractPriorMarkerFromFsupgrade($priorContents, $priorStubAbs);
        // The `priorOpenemrVersion` marker in the prior file is the
        // PRIOR CYCLE's value (e.g. "8.1.0" in fsupgrade-11.sh, from the
        // rel-810 cut). That's the search anchor. The REPLACEMENT is
        // this cut's derived last-shipped value (e.g. "8.1.1" for rel-820).
        $nextContents = $this->deriveNextFromPrior(
            $priorContents,
            $current,
            $next,
            $priorFileMarker,
            $priorOpenemrVersion,
            $priorStubAbs,
        );

        $changedFiles = [];

        // (1) Bump the three docker-version files.
        foreach (self::DOCKER_VERSION_PATHS as $relPath) {
            $abs = $projectDir . '/' . $relPath;
            if (file_put_contents($abs, $next . "\n") === false) {
                throw new \RuntimeException('Cannot write ' . $abs);
            }
            $changedFiles[] = $relPath;
        }

        // (2) Write the next fsupgrade-(N+1).sh (contents pre-derived
        // above so the write itself cannot throw a parse/marker error).
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
     * The `priorOpenemrVersion` marker to embed in the newly-scaffolded
     * fsupgrade-(N+1).sh. Semantically: the LAST SHIPPED version from
     * the prior release line — the version fsupgrade-(N+1).sh will
     * upgrade FROM when it eventually runs against a customer's install.
     *
     * Historical shape (see fsupgrade-10 / fsupgrade-11 on rel-810 tip):
     *   fsupgrade-10.sh (created for 8.1.0 line): priorOpenemrVersion="8.0.0"
     *   fsupgrade-11.sh (created for 8.1.1 release): priorOpenemrVersion="8.1.0"
     * At rel-820 cut, fsupgrade-12.sh's prior marker must be "8.1.1"
     * (the highest sql/*_upgrade.sql left-side), NOT "8.2.0" (the target).
     *
     * Derivation (canonical): scan sql/*_upgrade.sql, find the file with
     * the highest LEFT version via version_compare — that's the last
     * shipped upgrade bridge, so its left is the last shipped version.
     *
     * Patch-prep override: MutatorContext::$fromVersion is set by
     * PatchPrepCommand to the immediate prior patch version (e.g., 8.1.0
     * when preparing 8.1.1). That's exactly the prior-shipped-version
     * shape the sql scan would derive too, so it's semantically the
     * same, but the override bypasses filesystem state and is the
     * authoritative signal on that path.
     *
     * Defensive invariant: whichever derivation path is used, the
     * result must be STRICTLY LESS than the target version. If it's
     * equal, one of two bugs is in play:
     *   (a) the old target-based derivation snuck back in, or
     *   (b) a mutator earlier in the list (e.g. SqlUpgradeSkeletonMutator)
     *       already wrote a `<target>-to-<next>_upgrade.sql` file, which
     *       promoted the sql-scan result up to the target.
     * Both are load-bearing bugs — throw with a clear message rather
     * than emit a corrupt fsupgrade file.
     */
    private function derivePriorOpenemrVersion(MutatorContext $context): string
    {
        $priorVersion = $context->fromVersion
            ?? $this->derivePriorOpenemrVersionFromSql($context->projectDir);
        $target = $context->versionString();
        if (version_compare($priorVersion, $target, '<') !== true) {
            throw new \RuntimeException(sprintf(
                'priorOpenemrVersion %s must be strictly less than target %s. '
                . 'This usually indicates SqlUpgradeSkeletonMutator ran before '
                . 'DockerUpgradeScaffoldMutator; check the mutator list order '
                . 'in BranchCutCommand/PatchPrepCommand.',
                $priorVersion,
                $target,
            ));
        }
        return $priorVersion;
    }

    /**
     * Read the `priorOpenemrVersion="X.Y.Z"` value from the prior
     * fsupgrade-N.sh contents. Used as the search anchor when copying
     * the file forward — the substitution replaces this marker with
     * this cut's derived last-shipped version. Throws if the marker is
     * absent (same "load-bearing schema" invariant as
     * `deriveNextFromPrior`).
     */
    private function extractPriorMarkerFromFsupgrade(string $priorContents, string $priorFilePath): string
    {
        if (preg_match('/priorOpenemrVersion="(\d+\.\d+\.\d+)"/', $priorContents, $m) !== 1) {
            throw new \RuntimeException(sprintf(
                'fsupgrade scaffold cannot find priorOpenemrVersion="X.Y.Z" marker in %s; '
                . 'refusing to copy the file forward without a search anchor for the version substitution',
                $priorFilePath,
            ));
        }
        return $m[1];
    }

    /**
     * Idempotency helper: scan sql/*_upgrade.sql for the highest LEFT
     * version STRICTLY LESS THAN target, returning the "pre-scaffold
     * view" of the derivation. Bridges with left >= target are ignored
     * — those are either the sibling SqlUpgradeSkeletonMutator's output
     * (master-side post-scaffold) or an anomalous newer bridge; either
     * way they don't reflect the last-shipped-version state we
     * scaffolded from.
     *
     * Returns null when the pre-scaffold view produces no valid
     * candidate (e.g., sql/ is empty, or every bridge is at-or-above
     * target). A null return skips the idempotency check and lets
     * `derivePriorOpenemrVersion` produce the load-bearing error.
     */
    private function derivePriorOpenemrVersionFromSqlIgnoringAtOrAboveTarget(
        string $projectDir,
        string $targetVersion,
    ): ?string {
        $sqlDir = $projectDir . '/sql';
        $files = glob($sqlDir . '/*-to-*_upgrade.sql');
        if ($files === false || $files === []) {
            return null;
        }
        $highest = null;
        foreach ($files as $file) {
            $name = basename($file);
            if (preg_match('/^(\d+)_(\d+)_(\d+)-to-(\d+)_(\d+)_(\d+)_upgrade\.sql$/', $name, $m) !== 1) {
                continue;
            }
            $left = $m[1] . '.' . $m[2] . '.' . $m[3];
            if (version_compare($left, $targetVersion, '>=')) {
                continue;
            }
            if ($highest === null || version_compare($left, $highest, '>')) {
                $highest = $left;
            }
        }
        return $highest;
    }

    /**
     * Scan sql/*_upgrade.sql for the file with the highest LEFT
     * version via version_compare, and return its left as the prior
     * shipped version. Filenames match `X_Y_Z-to-A_B_C_upgrade.sql`
     * with underscore-separated version segments.
     */
    private function derivePriorOpenemrVersionFromSql(string $projectDir): string
    {
        $sqlDir = $projectDir . '/sql';
        $files = glob($sqlDir . '/*-to-*_upgrade.sql');
        if ($files === false || $files === []) {
            throw new \RuntimeException(
                'Cannot derive priorOpenemrVersion: no sql/*-to-*_upgrade.sql files found in ' . $sqlDir,
            );
        }
        $highest = null;
        foreach ($files as $file) {
            $name = basename($file);
            if (preg_match('/^(\d+)_(\d+)_(\d+)-to-(\d+)_(\d+)_(\d+)_upgrade\.sql$/', $name, $m) !== 1) {
                continue;
            }
            $left = $m[1] . '.' . $m[2] . '.' . $m[3];
            if ($highest === null || version_compare($left, $highest, '>')) {
                $highest = $left;
            }
        }
        if ($highest === null) {
            throw new \RuntimeException(
                'Cannot derive priorOpenemrVersion: no sql/X_Y_Z-to-A_B_C_upgrade.sql files matched in ' . $sqlDir,
            );
        }
        return $highest;
    }

    /**
     * Copy the prior fsupgrade-N.sh in full and apply exactly five
     * line-level substitutions to produce fsupgrade-(N+1).sh. All other
     * content (shellcheck directives, blank lines, upgrade body, trailing
     * newline) is preserved byte-for-byte.
     *
     * The prior-file marker for `priorOpenemrVersion` is what fsupgrade-N.sh
     * currently contains (e.g., "8.1.0" — the LAST shipped from the
     * cycle that created it). The replacement is THIS cut's derived
     * last-shipped version (e.g., "8.1.1"). Both may match on very rare
     * degenerate cycles; the substitution is idempotent in that case.
     *
     * If any of the five expected anchor patterns is missing from the
     * prior file, we throw rather than silently producing a corrupt
     * output — the prior file's shape is a load-bearing schema.
     */
    private function deriveNextFromPrior(
        string $priorContents,
        int $current,
        int $next,
        string $priorFileMarker,
        string $newFileMarker,
        string $priorFilePath,
    ): string {
        $substitutions = [
            [
                '# Upgrade number ' . $current . ' for OpenEMR docker',
                '# Upgrade number ' . $next . ' for OpenEMR docker',
            ],
            [
                '#  From prior version ' . $priorFileMarker . ' (needed for the sql upgrade script).',
                '#  From prior version ' . $newFileMarker . ' (needed for the sql upgrade script).',
            ],
            [
                'priorOpenemrVersion="' . $priorFileMarker . '"',
                'priorOpenemrVersion="' . $newFileMarker . '"',
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
