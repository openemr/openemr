<?php

/**
 * A CQL Concept: several codes meaning the same thing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final readonly class Concept
{
    /**
     * @param list<Code> $codes
     */
    public function __construct(
        public array $codes = [],
        public ?string $display = null,
    ) {
    }

    public function hasMatch(mixed $other): bool
    {
        return Clinical::codesInList(Clinical::toCodeList($other), $this->codes);
    }
}
