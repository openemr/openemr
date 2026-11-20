<?php

/**
 * An inclusive range of ages in whole years: "18-65", "18+" or "40".
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
    /** The upper bound "18+" stands for. */
    public const OPEN_MAX = 150;

    public function __construct(
        public int $min,
        public int $max,
    ) {
        if ($min < 0 || $max < $min) {
            throw new \DomainException('Age range must have 0 <= min <= max');
        }
    }

    public static function fromString(string $value, string $path): self
    {
        $value = trim($value);
        if (preg_match('/^(\d{1,3})\s*-\s*(\d{1,3})$/', $value, $matches) === 1) {
            $min = (int) $matches[1];
            $max = (int) $matches[2];
            if ($max < $min) {
                throw SeedFileException::at($path, 'the first age must not be larger than the second');
            }
            return new self($min, $max);
        }
        if (preg_match('/^(\d{1,3})\s*\+$/', $value, $matches) === 1) {
            return new self((int) $matches[1], self::OPEN_MAX);
        }
        if (preg_match('/^\d{1,3}$/', $value) === 1) {
            return new self((int) $value, (int) $value);
        }
        throw SeedFileException::at($path, 'expected an age range like "0-90", "18+" or "40"');
    }

    public function contains(int $age): bool
    {
        return $age >= $this->min && $age <= $this->max;
    }

    public function toString(): string
    {
        return $this->min . '-' . $this->max;
    }
}
