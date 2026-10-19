<?php

/**
 * UCUM unit validation and conversion as cql-execution uses them: ucum-lhc
 * 7.1.9's validateUnitString and convertUnitTo (UcumLhcUtils), with the
 * unit and prefix tables. See UcumData for the ucum-lhc notice.
 *
 * Conversions are those ucum-lhc makes without a molecular weight or
 * charge, which cql-execution never supplies: between units of the same
 * dimension and the same mole and equivalent exponents.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Ucum;

use OpenEMR\Cqm\Cql\Util\JavaScript;

final class Ucum
{
    private static ?self $instance = null;

    /** @var array<string, UcumUnit> */
    private array $byCode = [];

    /** @var array<string, UcumUnit> the first unit of each name */
    private array $byName = [];

    /** @var array<string, array{float, ?int}> */
    private array $prefixes = [];

    private function __construct()
    {
        foreach (UcumData::PREFIXES as [$code, $value, $exponent]) {
            $this->prefixes[$code] = [$value, $exponent];
        }
        foreach (UcumData::UNITS as [$code, $name, $magnitude, $dim, $cnv, $cnvPfx, $special, $arbitrary, $moleExp, $equivalentExp, $loinc]) {
            $unit = new UcumUnit($code, $magnitude, $dim, $cnv, $cnvPfx, $special, $arbitrary, $moleExp, $equivalentExp, $loinc);
            $this->byCode[$code] = $unit;
            $this->byName[$name] ??= $unit;
        }
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * The table unit with this code. It is shared: clone it before changing it.
     */
    public function unitByCode(string $code): ?UcumUnit
    {
        return $code === '' ? null : ($this->byCode[$code] ?? null);
    }

    /**
     * The first table unit with this name. It is shared: clone it before changing it.
     */
    public function unitByName(string $name): ?UcumUnit
    {
        return $this->byName[$name] ?? null;
    }

    /**
     * @return array{float, ?int}|null the prefix value and its power of ten
     */
    public function prefix(string $code): ?array
    {
        return $this->prefixes[$code] ?? null;
    }

    /**
     * Whether an expression is valid UCUM as written: it parses without
     * any repair (ucum-lhc's status "valid").
     */
    public function isValid(string $unitString): bool
    {
        return $this->specifiedUnit($unitString)[1];
    }

    /**
     * The unit an expression stands for, including one ucum-lhc reaches only
     * by repairing the expression; null when there is none.
     *
     * @return array{?UcumUnit, bool} the unit and whether the expression is valid as written
     */
    public function specifiedUnit(string $unitString): array
    {
        $unitString = JavaScript::trim($unitString);
        if ($unitString === '') {
            return [null, false];
        }
        $unit = $this->unitByCode($unitString);
        if ($unit !== null) {
            return [$unit, true];
        }
        try {
            [$unit, $origString] = (new UnitStringParser($this))->parse($unitString);
        } catch (UcumException) {
            return [null, false];
        }
        return [$unit, $unit !== null && $origString === $unitString];
    }

    /**
     * Converts a value between two unit expressions; null when ucum-lhc's
     * conversion does not succeed.
     */
    public function convert(string $fromUnit, float $value, string $toUnit): ?float
    {
        $fromUnit = JavaScript::trim($fromUnit);
        $toUnit = JavaScript::trim($toUnit);
        if ($fromUnit === '' || $toUnit === '' || is_nan($value)) {
            return null;
        }
        [$from] = $this->specifiedUnit($fromUnit);
        [$to] = $this->specifiedUnit($toUnit);
        if ($from === null || $to === null) {
            return null;
        }
        if ($from->moleExp !== $to->moleExp || $from->equivalentExp !== $to->equivalentExp) {
            // Moles or equivalents to mass need a molecular weight or charge.
            return null;
        }
        try {
            return $to->convertFrom($value, $from);
        } catch (UcumException) {
            return null;
        }
    }
}
