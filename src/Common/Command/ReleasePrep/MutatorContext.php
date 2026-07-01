<?php

/**
 * Context passed to every release-prep mutator. Holds the parsed target
 * version and the project root each mutator operates against, plus
 * optional inputs (image digest, rel branch identifier) supplied by the
 * conductor workflow. Existing mutators ignore the optional fields they
 * don't need; PostReleaseTargetsMutator requires `relBranch` to know
 * which row of release-targets.yml to mutate.
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
        public ?string $imageDigest = null,
        public ?string $relBranch = null,
    ) {
        if ($imageDigest !== null && preg_match('/^sha256:[0-9a-f]{64}$/', $imageDigest) !== 1) {
            throw new \InvalidArgumentException(
                'imageDigest must match sha256:<64-hex>; got: ' . $imageDigest,
            );
        }
        if ($relBranch !== null && preg_match('/^rel-\d+$/', $relBranch) !== 1) {
            throw new \InvalidArgumentException(
                'relBranch must match rel-<digits>; got: ' . $relBranch,
            );
        }
    }

    public static function fromVersionString(
        string $projectDir,
        string $version,
        ?string $imageDigest = null,
        ?string $relBranch = null,
    ): self {
        if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $version, $m) !== 1) {
            throw new \InvalidArgumentException(
                'Target version must be MAJOR.MINOR.PATCH; got: ' . $version,
            );
        }
        return new self($projectDir, (int) $m[1], (int) $m[2], (int) $m[3], $imageDigest, $relBranch);
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
