<?php

/**
 * Context passed to every release-prep mutator. Holds the parsed target
 * version and the project root each mutator operates against, plus
 * optional inputs (image digest) supplied by the conductor workflow.
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
    ) {
        if ($imageDigest !== null && preg_match('/^sha256:[0-9a-f]{64}$/', $imageDigest) !== 1) {
            throw new \InvalidArgumentException(
                'imageDigest must match sha256:<64-hex>; got: ' . $imageDigest,
            );
        }
    }

    public static function fromVersionString(string $projectDir, string $version, ?string $imageDigest = null): self
    {
        if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $version, $m) !== 1) {
            throw new \InvalidArgumentException(
                'Target version must be MAJOR.MINOR.PATCH; got: ' . $version,
            );
        }
        return new self($projectDir, (int) $m[1], (int) $m[2], (int) $m[3], $imageDigest);
    }

    public function versionString(): string
    {
        return sprintf('%d.%d.%d', $this->major, $this->minor, $this->patch);
    }
}
