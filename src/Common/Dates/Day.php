<?php

/**
 * A calendar day: a date with no time and no time zone.
 *
 * The shared Day type of the dates contract. A stored DATE or DATETIME
 * becomes a Day through fromStored(), with the same answers as the
 * insurance coverage day (a blank, zero or impossible value is no day),
 * so the coverage rule and every other "in force on a date" rule read a
 * stored value the same way.
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

final readonly class Day implements \Stringable
{
    private function __construct(
        public int $year,
        public int $month,
        public int $day,
    ) {
    }

    /**
     * @throws InvalidArgumentException when the three numbers aren't a real day
     */
    public static function of(int $year, int $month, int $day): self
    {
        if ($year < 1 || $year > 9999 || !checkdate($month, $day, $year)) {
            throw new InvalidArgumentException(sprintf('%04d-%02d-%02d is not a calendar day', $year, $month, $day));
        }
        return new self($year, $month, $day);
    }

    /**
     * Exactly YYYY-MM-DD, a real day; anything else is null.
     */
    public static function fromIso(string $text): ?self
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $text, $m) !== 1) {
            return null;
        }
        $year = (int)$m[1];
        $month = (int)$m[2];
        $day = (int)$m[3];
        return $year >= 1 && checkdate($month, $day, $year) ? new self($year, $month, $day) : null;
    }

    /**
     * A stored DATE or DATETIME as its calendar day, or null when it is
     * blank, zero (0000-00-00), a zero day or month (2026-05-00) or not a
     * day. The time is dropped. Anything but a string is blank, so callers
     * need not narrow what a row holds first.
     */
    public static function fromStored(mixed $value): ?self
    {
        if (!is_string($value) || strlen($value) < 10) {
            return null;
        }
        return self::fromIso(substr($value, 0, 10));
    }

    /**
     * The calendar day of an instant, in the instant's own time zone.
     */
    public static function fromDateTime(DateTimeInterface $at): self
    {
        return new self((int)$at->format('Y'), (int)$at->format('n'), (int)$at->format('j'));
    }

    public function iso(): string
    {
        return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
    }

    /**
     * The day's first instant (00:00:00) in a time zone; the site's when none is given.
     */
    public function firstInstantIn(?DateTimeZone $zone = null): DateTimeImmutable
    {
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', $this->iso(), $zone ?? SiteClock::zone());
        if ($start === false) {
            throw new \LogicException('a Day always formats as a date');
        }
        return $start;
    }

    /**
     * The day's first instant as a DATETIME column stores it: 'Y-m-d 00:00:00'.
     */
    public function storedStart(): string
    {
        return $this->iso() . ' 00:00:00';
    }

    public function plusDays(int $days): self
    {
        $moved = (new DateTimeImmutable($this->iso(), new DateTimeZone('UTC')))->modify(sprintf('%+d days', $days));
        return self::fromDateTime($moved);
    }

    public function compare(self $other): int
    {
        return [$this->year, $this->month, $this->day] <=> [$other->year, $other->month, $other->day];
    }

    public function isBefore(self $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function isAfter(self $other): bool
    {
        return $this->compare($other) > 0;
    }

    public function equals(self $other): bool
    {
        return $this->compare($other) === 0;
    }

    public function __toString(): string
    {
        return $this->iso();
    }
}
