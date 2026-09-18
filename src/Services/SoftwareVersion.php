<?php

/**
 * SoftwareVersion - Value object for the running software version
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Core\OEGlobalsBag;

final readonly class SoftwareVersion implements \Stringable
{
    public string $base;
    public string $full;

    public function __construct(
        public int $major,
        public int $minor,
        public int $patch,
        public string $tag,
        public int $realpatch,
        public int $database,
        public int $acl,
    ) {
        $this->base = "{$this->major}.{$this->minor}.{$this->patch}";

        $this->full = match (true) {
            ($this->tag !== '' && $this->realpatch > 0) => "{$this->base}{$this->tag}.{$this->realpatch}",
            ($this->tag !== '') => "{$this->base}{$this->tag}",
            ($this->realpatch > 0) => "{$this->base}.{$this->realpatch}",
            default => $this->base,
        };
    }

    public function __toString(): string
    {
        return $this->full;
    }

    public static function fromGlobals(OEGlobalsBag $globals): self
    {
        return new self(
            major: $globals->getInt('v_major'),
            minor: $globals->getInt('v_minor'),
            patch: $globals->getInt('v_patch'),
            tag: $globals->getString('v_tag'),
            realpatch: $globals->getInt('v_realpatch'),
            database: $globals->getInt('v_database'),
            acl: $globals->getInt('v_acl'),
        );
    }
}
