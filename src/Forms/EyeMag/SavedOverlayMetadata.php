<?php

/**
 * Immutable metadata for a reusable provider drawing overlay.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

use JsonSerializable;

final readonly class SavedOverlayMetadata implements JsonSerializable
{
    /** Record a saved overlay's stable key, name, image checksum, and archive state. */
    public function __construct(
        public string $key,
        public string $label,
        public string $sha256,
        public bool $deleted = false,
    ) {
    }

    /** Return renamed metadata while preserving the image identity and archive state. */
    public function withLabel(string $label): self
    {
        return new self($this->key, $label, $this->sha256, $this->deleted);
    }

    /** Mark an overlay unavailable for new selections while retaining its saved image identity. */
    public function archived(): self
    {
        return new self($this->key, $this->label, $this->sha256, true);
    }

    /**
     * Serialize versioned overlay metadata, including the archive marker only when set.
     *
     * @return array{version:1,key:string,label:string,sha256:string,deleted?:true}
     */
    public function jsonSerialize(): array
    {
        $metadata = ['version' => 1, 'key' => $this->key, 'label' => $this->label, 'sha256' => $this->sha256];
        if ($this->deleted) {
            $metadata['deleted'] = true;
        }
        return $metadata;
    }
}
