<?php

/**
 * A period of time, half-open: from its first instant (included) until its
 * end (not included). A missing side is open.
 *
 * Every inclusive range a person types ("from the 1st to the 4th") is a
 * Period from the first day's midnight until the midnight after the last
 * day, so a DATETIME with a time on the last day is inside it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Dates;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

final readonly class Period
{
    private function __construct(
        public ?DateTimeImmutable $from,
        public ?DateTimeImmutable $until,
    ) {
    }

    /**
     * @throws InvalidArgumentException when the end isn't after the start
     */
    public static function between(?DateTimeInterface $from, ?DateTimeInterface $until): self
    {
        $f = $from === null ? null : DateTimeImmutable::createFromInterface($from);
        $u = $until === null ? null : DateTimeImmutable::createFromInterface($until);
        if ($f !== null && $u !== null && $u <= $f) {
            throw new InvalidArgumentException('a period ends after it starts');
        }
        return new self($f, $u);
    }

    /**
     * The whole days from $first through $last, both included: [first 00:00, the day after last 00:00).
     *
     * @throws InvalidArgumentException when $last is before $first
     */
    public static function days(Day $first, Day $last, ?DateTimeZone $zone = null): self
    {
        if ($last->isBefore($first)) {
            throw new InvalidArgumentException('a range of days ends on or after its first day');
        }
        return new self($first->firstInstantIn($zone), $last->plusDays(1)->firstInstantIn($zone));
    }

    public function contains(DateTimeInterface $at): bool
    {
        return ($this->from === null || $at >= $this->from) && ($this->until === null || $at < $this->until);
    }

    /**
     * The two bounds as a DATETIME column stores the site's wall clock ('Y-m-d H:i:s'), null for an open side.
     *
     * @return array{?string, ?string}
     */
    public function storedBounds(): array
    {
        return [$this->from?->format('Y-m-d H:i:s'), $this->until?->format('Y-m-d H:i:s')];
    }
}
