<?php

/**
 * Context passed to every release-prep mutator. Holds the parsed target
 * version and the project root each mutator operates against, plus
 * optional inputs (rel branch identifier, prior rel branch identifier,
 * explicit fromVersion override) supplied by the conductor or
 * branch-cut/patch-prep workflows. Existing mutators ignore the optional
 * fields they don't need; PostReleaseTargetsMutator requires `relBranch`
 * to know which row of release-targets.yml to mutate;
 * TranslationFileCopyFromPriorRelMutator requires `prevRelBranch` to know
 * which rel branch to fetch the translation blob from;
 * SqlUpgradeSkeletonMutator prefers `fromVersion` when set (used by
 * patch-prep on both sides, where version.php on the rel branch has
 * already been bumped past the value the skeleton needs to anchor at).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep;

final readonly class MutatorContext
{
    public function __construct(
        public string $projectDir,
        public int $major,
        public int $minor,
        public int $patch,
        public ?string $relBranch = null,
        public ?string $prevRelBranch = null,
        public ?string $fromVersion = null,
    ) {
        if ($relBranch !== null && preg_match('/^rel-\d+$/', $relBranch) !== 1) {
            throw new \InvalidArgumentException(
                'relBranch must match rel-<digits>; got: ' . $relBranch,
            );
        }
        if ($prevRelBranch !== null && preg_match('/^rel-\d+$/', $prevRelBranch) !== 1) {
            throw new \InvalidArgumentException(
                'prevRelBranch must match rel-<digits>; got: ' . $prevRelBranch,
            );
        }
        if ($fromVersion !== null) {
            if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $fromVersion, $fm) !== 1) {
                throw new \InvalidArgumentException(
                    'fromVersion must match MAJOR.MINOR.PATCH; got: ' . $fromVersion,
                );
            }
            $fromMajor = (int) $fm[1];
            $fromMinor = (int) $fm[2];
            $fromPatch = (int) $fm[3];
            if ($fromMajor !== $major || $fromMinor !== $minor) {
                throw new \InvalidArgumentException(sprintf(
                    'fromVersion must share major.minor with target (target=%d.%d.%d, got fromVersion=%s)',
                    $major,
                    $minor,
                    $patch,
                    $fromVersion,
                ));
            }
            if ($fromPatch !== $patch - 1) {
                throw new \InvalidArgumentException(sprintf(
                    'fromVersion must be the immediate prior patch of target (target=%d.%d.%d expects fromVersion=%d.%d.%d, got %s)',
                    $major,
                    $minor,
                    $patch,
                    $major,
                    $minor,
                    $patch - 1,
                    $fromVersion,
                ));
            }
        }
    }

    public static function fromVersionString(
        string $projectDir,
        string $version,
        ?string $relBranch = null,
        ?string $prevRelBranch = null,
        ?string $fromVersion = null,
    ): self {
        if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $version, $m) !== 1) {
            throw new \InvalidArgumentException(
                'Target version must be MAJOR.MINOR.PATCH; got: ' . $version,
            );
        }
        return new self(
            $projectDir,
            (int) $m[1],
            (int) $m[2],
            (int) $m[3],
            $relBranch,
            $prevRelBranch,
            $fromVersion,
        );
    }

    /**
     * Render the git tag name corresponding to this context's version,
     * matching the canonical openemr scheme: `v` + version with `_`
     * replacing `.` (e.g. 8.1.1 -> v8_1_1). Used by post-release mutators
     * that need to pin a rel branch row's openemr_version_ref to the
     * just-created tag.
     */
    public function tagName(): string
    {
        return sprintf('v%d_%d_%d', $this->major, $this->minor, $this->patch);
    }

    public function versionString(): string
    {
        return sprintf('%d.%d.%d', $this->major, $this->minor, $this->patch);
    }
}
