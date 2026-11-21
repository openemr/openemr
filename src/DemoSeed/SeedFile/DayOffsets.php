<?php

/**
 * A range of days counted from the day the loader runs, like "-7..21" (both included).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class DayOffsets
{
    public function __construct(
        public int $from,
        public int $to,
    ) {
        if ($to < $from) {
            throw new \DomainException('Day offsets must not end before they start');
        }
    }

    public static function parse(MapReader $map, string $key): self
    {
        $value = $map->raw($key);
        if (is_int($value)) {
            return new self($value, $value);
        }
        if (is_string($value) && preg_match('/^\s*(-?\d{1,4})\s*\.\.\s*(-?\d{1,4})\s*$/', $value, $matches) === 1) {
            $from = (int) $matches[1];
            $to = (int) $matches[2];
            if ($to >= $from) {
                return new self($from, $to);
            }
        }
        throw SeedFileException::at($map->pathOf($key), 'expected a day like 3 or a range like "-7..21"');
    }
}
