<?php

/**
 * The CQL Ratio type, two quantities, ported from cql-execution 3.3.2.
 * Ratios are equal when their quotients are equal, so 1:2 equals 2:4.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final readonly class Ratio implements \Stringable
{
    public function __construct(
        public Quantity $numerator,
        public Quantity $denominator,
    ) {
    }

    public function equals(mixed $other): ?bool
    {
        if (!$other instanceof self) {
            return false;
        }
        // Both quotients first: dividing can throw on a unit that does not combine.
        $quotient = $this->numerator->dividedBy($this->denominator);
        $otherQuotient = $other->numerator->dividedBy($other->denominator);
        return $quotient?->equals($otherQuotient);
    }

    public function equivalent(mixed $other): bool
    {
        return $this->equals($other) ?? false;
    }

    public function toString(): string
    {
        return $this->numerator->toString() . ' : ' . $this->denominator->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
