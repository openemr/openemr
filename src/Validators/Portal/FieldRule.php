<?php

/**
 * One column's validation rule for the patient portal's record API, as the Phreeze field maps
 * declared it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Validators\Portal;

final readonly class FieldRule
{
    /**
     * @param ?positive-int $size the declared size; checked as a length for every type
     * @param bool $optional an empty value is allowed (the column has a default or is auto-filled)
     */
    private function __construct(
        public bool $numeric,
        public ?int $size,
        public bool $optional,
    ) {
    }

    /**
     * @param positive-int $size
     */
    public static function number(int $size, bool $optional = false): self
    {
        return new self(true, $size, $optional);
    }

    /**
     * @param ?positive-int $size null for unbounded text
     */
    public static function text(?int $size = null): self
    {
        return new self(false, $size, false);
    }
}
