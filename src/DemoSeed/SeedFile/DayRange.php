<?php

/**
 * A number of days, fixed ("90") or picked from a range ("30-700").
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class DayRange
{
    public function __construct(
        public int $min,
        public int $max,
    ) {
        if ($min < 0 || $max < $min) {
            throw new \DomainException('Day range must have 0 <= min <= max');
        }
    }

    public static function fromValue(mixed $value, string $path): self
    {
        if (is_int($value) && $value >= 0) {
            return new self($value, $value);
        }
        if (is_string($value) && preg_match('/^\s*(\d{1,5})\s*-\s*(\d{1,5})\s*$/', $value, $matches) === 1) {
            $min = (int) $matches[1];
            $max = (int) $matches[2];
            if ($max >= $min) {
                return new self($min, $max);
            }
        }
        throw SeedFileException::at($path, 'expected a number of days like 90 or a range like "30-700"');
    }

    public static function optional(MapReader $map, string $key): ?self
    {
        return $map->has($key) ? self::fromValue($map->raw($key), $map->pathOf($key)) : null;
    }
}
