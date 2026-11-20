<?php

/**
 * A parsed seed file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class SeedFile
{
    /**
     * @param list<string> $sectionsNotLoaded Sections the file has that this
     *        version of the loader recognizes but doesn't load yet.
     */
    public function __construct(
        public int $seed,
        public SyntheaOptions $synthea,
        public array $sectionsNotLoaded,
    ) {
    }

    public function withSynthea(SyntheaOptions $synthea): self
    {
        return new self($this->seed, $synthea, $this->sectionsNotLoaded);
    }
}
