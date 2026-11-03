<?php

/**
 * Isolated tests for the UCUM port's exponents at the edge of PHP's integer
 * range: a unit whose exponent, or the sum of two, no longer fits is invalid.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Cql\Ucum;

use OpenEMR\Cqm\Cql\Ucum\Ucum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UcumExponentRangeTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function outOfRange(): array
    {
        return [
            'L to a power whose dimension overflows' => ['L4000000000000000000'],
            'the same, negative' => ['L-4000000000000000000'],
            'one past the largest power of L that fits' => ['L3074457345618258603'],
            'a power past PHP_INT_MAX itself' => ['L99999999999999999999'],
            'a power one past PHP_INT_MAX' => ['m9223372036854775808'],
            'a power one below PHP_INT_MIN' => ['m-9223372036854775809'],
            'a product whose dimensions overflow' => ['m4611686018427387904.m4611686018427387904'],
            'a quotient whose dimensions overflow' => ['m-4611686018427387905/m4611686018427387904'],
            'a product whose mole exponent overflows' => ['mol9223372036854775807.mol'],
            'a product whose equivalent exponent overflows' => ['eq9223372036854775807.eq'],
            'a quotient whose mole exponent overflows' => ['mol-9223372036854775808/mol'],
            'a quotient whose equivalent exponent overflows' => ['eq-9223372036854775808/eq'],
            'an overflowing power with an annotation' => ['L4000000000000000000{x}'],
            'an overflowing power in parentheses' => ['(L4000000000000000000)'],
        ];
    }

    #[DataProvider('outOfRange')]
    public function testAnExponentPastTheIntegerRangeIsInvalid(string $unit): void
    {
        $ucum = Ucum::instance();
        $this->assertSame([null, false], $ucum->specifiedUnit($unit));
        $this->assertFalse($ucum->isValid($unit));
        $this->assertNull($ucum->convert($unit, 1.0, 'L'));
    }

    /**
     * @return array<string, array{string, list<int>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function inRange(): array
    {
        return [
            'L cubed' => ['L3', [9, 0, 0, 0, 0, 0, 0]],
            'the largest power of L that fits' => ['L3074457345618258602', [9223372036854775806, 0, 0, 0, 0, 0, 0]],
            'm to PHP_INT_MAX' => ['m9223372036854775807', [PHP_INT_MAX, 0, 0, 0, 0, 0, 0]],
            'm to PHP_INT_MIN' => ['m-9223372036854775808', [PHP_INT_MIN, 0, 0, 0, 0, 0, 0]],
            'a product that reaches PHP_INT_MAX' => ['m4611686018427387903.m4611686018427387904', [PHP_INT_MAX, 0, 0, 0, 0, 0, 0]],
            'a quotient that reaches PHP_INT_MIN' => ['m-4611686018427387904/m4611686018427387904', [PHP_INT_MIN, 0, 0, 0, 0, 0, 0]],
        ];
    }

    /**
     * @param list<int> $dim
     */
    #[DataProvider('inRange')]
    public function testAnExponentInRangeIsValid(string $unit, array $dim): void
    {
        [$parsed, $valid] = Ucum::instance()->specifiedUnit($unit);
        $this->assertTrue($valid);
        $this->assertNotNull($parsed);
        $this->assertSame($dim, $parsed->dim);
    }
}
