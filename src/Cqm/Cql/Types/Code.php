<?php

/**
 * A CQL Code: a code in a code system, ported from cql-execution.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final readonly class Code
{
    public function __construct(
        public string $code,
        public ?string $system = null,
        public ?string $version = null,
        public ?string $display = null,
    ) {
    }

    /**
     * Whether this code matches a code string, a code, a concept, a value
     * set or a list of them. Codes match on code and system.
     */
    public function hasMatch(mixed $other): bool
    {
        if (is_string($other)) {
            return $other === $this->code;
        }
        return Clinical::codesInList(Clinical::toCodeList($other), [$this]);
    }
}
