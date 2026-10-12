<?php

/**
 * A UCUM unit: a magnitude over the seven base dimensions, optionally a
 * special (non-ratio) conversion function. Ported from ucum-lhc 7.1.9's
 * Unit; see UcumData for the ucum-lhc notice. Only the fields that
 * parsing and conversion use are kept; display names and print symbols are
 * not.
 *
 * Units are copied with clone before any change, as ucum-lhc does; the
 * dimension vector is an array, so a clone is a full copy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Ucum;

use OpenEMR\Cqm\Cql\Util\Fdlibm;
use OpenEMR\Cqm\Cql\Util\JavaScript;

final class UcumUnit
{
    /**
     * @param list<int> $dim
     */
    public function __construct(
        public string $csCode = '',
        public float $magnitude = 1.0,
        public array $dim = [0, 0, 0, 0, 0, 0, 0],
        public ?string $cnv = null,
        public float $cnvPfx = 1.0,
        public bool $isSpecial = false,
        public bool $isArbitrary = false,
        public int $moleExp = 0,
        public int $equivalentExp = 0,
        public bool $fromLoinc = false,
    ) {
    }

    /**
     * A unit that is just a number, as ucum-lhc builds one: a magnitude of
     * zero (or NaN) falls back to 1, through JavaScript's `||` default.
     */
    public static function forNumber(string $code, float $value): self
    {
        return new self($code, $value == 0 || is_nan($value) ? 1.0 : $value);
    }

    public function isDimensionless(): bool
    {
        return $this->dim === [0, 0, 0, 0, 0, 0, 0];
    }

    /**
     * Converts a value in another unit to this one.
     *
     * @throws UcumException when the units are not commensurable or are arbitrary
     */
    public function convertFrom(float $value, self $from): float
    {
        if ($this->isArbitrary) {
            throw new UcumException("Attempt to convert to arbitrary unit {$this->csCode}");
        }
        if ($from->isArbitrary) {
            throw new UcumException("Attempt to convert arbitrary unit {$from->csCode}");
        }
        if ($from->dim !== $this->dim) {
            throw new UcumException("{$from->csCode} cannot be converted to {$this->csCode}");
        }
        $x = $from->cnv !== null
            ? self::convertFromSpecial($from->cnv, $value * $from->cnvPfx) * $from->magnitude
            : $value * $from->magnitude;
        return $this->cnv !== null
            ? fdiv(self::convertToSpecial($this->cnv, fdiv($x, $this->magnitude)), $this->cnvPfx)
            : fdiv($x, $this->magnitude);
    }

    /**
     * @throws UcumException when either unit has a conversion function that cannot combine
     */
    public function multiplyThese(self $other): self
    {
        $result = clone $this;
        if ($result->cnv !== null) {
            if ($other->cnv !== null || !$other->isDimensionless()) {
                throw new UcumException('Attempt to multiply non-ratio unit');
            }
            $result->cnvPfx *= $other->magnitude;
        } elseif ($other->cnv !== null) {
            if (!$result->isDimensionless()) {
                throw new UcumException('Attempt to multiply non-ratio unit');
            }
            $result->cnvPfx = $other->cnvPfx * $result->magnitude;
            $result->magnitude = $other->magnitude;
            $result->cnv = $other->cnv;
        } else {
            $result->magnitude *= $other->magnitude;
        }
        $result->dim = array_map(static fn (int $a, int $b): int => $a + $b, $result->dim, $other->dim);
        $result->equivalentExp += $other->equivalentExp;
        $result->moleExp += $other->moleExp;
        $result->csCode = self::concatCodes($result->csCode, '.', $other->csCode);
        $result->isArbitrary = $result->isArbitrary || $other->isArbitrary;
        $result->isSpecial = $result->isSpecial || $other->isSpecial;
        return $result;
    }

    /**
     * @throws UcumException when either unit has a conversion function
     */
    public function divide(self $other): self
    {
        $result = clone $this;
        if ($result->cnv !== null) {
            throw new UcumException('Attempt to divide non-ratio unit');
        }
        if ($other->cnv !== null) {
            throw new UcumException('Attempt to divide by non-ratio unit');
        }
        $result->csCode = self::concatCodes($result->csCode, '/', $other->csCode);
        $result->magnitude = fdiv($result->magnitude, $other->magnitude);
        $result->dim = array_map(static fn (int $a, int $b): int => $a - $b, $result->dim, $other->dim);
        $result->moleExp -= $other->moleExp;
        $result->equivalentExp -= $other->equivalentExp;
        $result->isArbitrary = $result->isArbitrary || $other->isArbitrary;
        return $result;
    }

    /** Whether the units differ only by moles and mass, which needs a molecular weight. */
    public function isMolMassCommensurable(self $other, int $massDimension): bool
    {
        $mine = $this->dim;
        $mine[$massDimension] += $this->moleExp;
        $theirs = $other->dim;
        $theirs[$massDimension] += $other->moleExp;
        return $mine === $theirs;
    }

    private static function concatCodes(string $first, string $operator, string $second): string
    {
        return self::wrapCode($first) . $operator . self::wrapCode($second);
    }

    /** Parenthesizes a code that contains an operator, unless it is already enclosed. */
    private static function wrapCode(string $code): string
    {
        if (JavaScript::isNumericString($code)) {
            return $code;
        }
        if (
            (str_starts_with($code, '(') && str_ends_with($code, ')'))
            || (str_starts_with($code, '[') && str_ends_with($code, ']'))
        ) {
            return $code;
        }
        return preg_match('/[.\/* ]/', $code) === 1 ? "($code)" : $code;
    }

    /**
     * ucum-lhc looks the function up by its lower-cased name, which misses
     * the ones it registered in camel case (hpX, hpC, hpM, hpQ and
     * tanTimes100); converting with those fails, as it does there.
     *
     * @throws UcumException for an unknown function
     */
    private static function convertFromSpecial(string $function, float $x): float
    {
        return match (strtolower($function)) {
            'cel', 'degre' => $x + 273.15,
            'degf' => $x + 459.67,
            'ph' => Fdlibm::pow(10.0, -$x),
            'ln' => Fdlibm::exp($x),
            '2ln' => Fdlibm::exp($x / 2),
            'lg' => Fdlibm::pow(10.0, $x),
            '10lg' => Fdlibm::pow(10.0, $x / 10),
            '20lg' => Fdlibm::pow(10.0, $x / 20),
            '2lg', 'lgtimes2' => Fdlibm::pow(10.0, $x / 2),
            'ld' => Fdlibm::pow(2.0, $x),
            '100tan' => atan($x / 100),
            'sqrt' => $x * $x,
            'inv' => fdiv(1.0, $x),
            default => throw new UcumException("Unknown conversion function $function"),
        };
    }

    /**
     * @throws UcumException for an unknown function
     */
    private static function convertToSpecial(string $function, float $x): float
    {
        return match (strtolower($function)) {
            'cel', 'degre' => $x - 273.15,
            'degf' => $x - 459.67,
            'ph' => -self::log($x) / M_LN10,
            'ln' => self::log($x),
            '2ln' => 2 * self::log($x),
            'lg' => self::log($x) / M_LN10,
            '10lg' => 10 * self::log($x) / M_LN10,
            '20lg' => 20 * self::log($x) / M_LN10,
            '2lg', 'lgtimes2' => 2 * self::log($x) / M_LN10,
            'ld' => self::log($x) / M_LN2,
            '100tan' => tan($x) * 100,
            'sqrt' => $x < 0 ? NAN : sqrt($x),
            'inv' => fdiv(1.0, $x),
            default => throw new UcumException("Unknown conversion function $function"),
        };
    }

    /** Math.log, as V8 computes it. */
    private static function log(float $x): float
    {
        return Fdlibm::log($x);
    }
}
