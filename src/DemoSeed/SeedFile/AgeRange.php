<?php

/**
 * An inclusive range of ages in whole years, written "min-max" in the seed file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class AgeRange
{
    public function __construct(
        public int $min,
        public int $max,
    ) {
        if ($min < 0 || $max < $min) {
            throw new \DomainException('Age range must have 0 <= min <= max');
        }
    }

    /**
     * Parses "min-max", e.g. "0-90".
     */
    public static function fromString(string $value, string $path): self
    {
        if (preg_match('/^\s*(\d{1,3})\s*-\s*(\d{1,3})\s*$/', $value, $matches) !== 1) {
            throw SeedFileException::at($path, 'expected an age range like "0-90"');
        }
        $min = (int) $matches[1];
        $max = (int) $matches[2];
        if ($max < $min) {
            throw SeedFileException::at($path, 'the first age must not be larger than the second');
        }
        return new self($min, $max);
    }

    public function toString(): string
    {
        return $this->min . '-' . $this->max;
    }
}
