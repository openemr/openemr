<?php

/**
 * A time window within a day, like "08:30-12:00", in minutes after midnight.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class TimeWindow
{
    public function __construct(
        public int $start,
        public int $end,
    ) {
        if ($start < 0 || $end > 24 * 60 || $end <= $start) {
            throw new \DomainException('Time window must end after it starts, within one day');
        }
    }

    public static function fromString(string $value, string $path): self
    {
        if (preg_match('/^\s*(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s*$/', $value, $matches) === 1) {
            $start = self::minutesOf((int) $matches[1], (int) $matches[2]);
            $end = self::minutesOf((int) $matches[3], (int) $matches[4]);
            if ($start !== null && $end !== null && $end > $start) {
                return new self($start, $end);
            }
        }
        throw SeedFileException::at($path, 'expected a time window like "08:30-12:00"');
    }

    /**
     * Windows separated by commas, in order and not overlapping.
     *
     * @return non-empty-list<self>
     */
    public static function listFromString(string $value, string $path): array
    {
        $windows = [];
        foreach (explode(',', $value) as $part) {
            $window = self::fromString($part, $path);
            $previous = $windows === [] ? null : $windows[count($windows) - 1];
            if ($previous !== null && $window->start < $previous->end) {
                throw SeedFileException::at($path, 'time windows must be in order and not overlap');
            }
            $windows[] = $window;
        }
        return $windows;
    }

    public function minutes(): int
    {
        return $this->end - $this->start;
    }

    private static function minutesOf(int $hour, int $minute): ?int
    {
        return $hour <= 24 && $minute < 60 && $hour * 60 + $minute <= 24 * 60 ? $hour * 60 + $minute : null;
    }
}
