<?php

/**
 * Isolated tests for TypeCoerce — the mixed→typed coercion helpers used
 * across every ClaimRev service that reads QueryUtils row cells.
 *
 * These helpers are the load-bearing alternative to bare PHP casts: a
 * numeric string '12' becomes int 12, but a non-numeric string 'abc'
 * returns the default rather than silently coercing to 0. The tests
 * pin down both the happy path and each fallback, so a future change
 * that flips a default or accepts a previously-rejected input has to
 * update an explicit assertion.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\TypeCoerce;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/TypeCoerce.php';

class TypeCoerceTest extends TestCase
{
    // ---------------------------------------------------------------
    // asString()
    // ---------------------------------------------------------------

    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function asStringProvider(): array
    {
        return [
            'string passthrough'  => ['hello', 'hello'],
            'empty string'        => ['', ''],
            'int becomes string'  => [42, '42'],
            'zero int'            => [0, '0'],
            'negative int'        => [-7, '-7'],
            'float becomes string' => [1.5, '1.5'],
            'null returns default' => [null, ''],
            'true returns default' => [true, ''],
            'false returns default' => [false, ''],
            'array returns default' => [['a'], ''],
            'object returns default' => [new \stdClass(), ''],
        ];
    }

    #[DataProvider('asStringProvider')]
    public function testAsStringDefault(mixed $input, string $expected): void
    {
        $this->assertSame($expected, TypeCoerce::asString($input));
    }

    public function testAsStringRespectsCustomDefault(): void
    {
        $this->assertSame('N/A', TypeCoerce::asString(null, 'N/A'));
        $this->assertSame('N/A', TypeCoerce::asString(true, 'N/A'));
        // Real strings still passthrough — default is only used on fallback.
        $this->assertSame('hello', TypeCoerce::asString('hello', 'N/A'));
    }

    // ---------------------------------------------------------------
    // asInt()
    // ---------------------------------------------------------------

    /**
     * @return array<string, array{mixed, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function asIntProvider(): array
    {
        return [
            'int passthrough'         => [42, 42],
            'zero'                    => [0, 0],
            'negative int'            => [-7, -7],
            'numeric string'          => ['123', 123],
            'numeric string negative' => ['-50', -50],
            'numeric string with decimal' => ['12.7', 12], // truncates
            'float'                   => [9.9, 9],
            'non-numeric string'      => ['abc', 0],
            'empty string'            => ['', 0],
            'null'                    => [null, 0],
            'bool true'               => [true, 0],
            'bool false'              => [false, 0],
            'array'                   => [[1], 0],
        ];
    }

    #[DataProvider('asIntProvider')]
    public function testAsIntDefault(mixed $input, int $expected): void
    {
        $this->assertSame($expected, TypeCoerce::asInt($input));
    }

    public function testAsIntRespectsCustomDefault(): void
    {
        $this->assertSame(99, TypeCoerce::asInt('abc', 99));
        $this->assertSame(99, TypeCoerce::asInt(null, 99));
        // Numeric input ignores the default.
        $this->assertSame(7, TypeCoerce::asInt('7', 99));
    }

    // ---------------------------------------------------------------
    // asFloat()
    // ---------------------------------------------------------------

    /**
     * @return array<string, array{mixed, float}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function asFloatProvider(): array
    {
        return [
            'float passthrough'       => [1.5, 1.5],
            'zero float'              => [0.0, 0.0],
            'int becomes float'       => [42, 42.0],
            'numeric string'          => ['1.25', 1.25],
            'numeric string int form' => ['10', 10.0],
            'negative numeric string' => ['-3.14', -3.14],
            'non-numeric string'      => ['abc', 0.0],
            'empty string'            => ['', 0.0],
            'null'                    => [null, 0.0],
            'bool'                    => [true, 0.0],
        ];
    }

    #[DataProvider('asFloatProvider')]
    public function testAsFloatDefault(mixed $input, float $expected): void
    {
        $this->assertSame($expected, TypeCoerce::asFloat($input));
    }

    public function testAsFloatRespectsCustomDefault(): void
    {
        $this->assertSame(0.01, TypeCoerce::asFloat('xyz', 0.01));
        $this->assertSame(0.01, TypeCoerce::asFloat(null, 0.01));
    }

    // ---------------------------------------------------------------
    // asBool()
    // ---------------------------------------------------------------

    /**
     * @return array<string, array{mixed, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function asBoolProvider(): array
    {
        return [
            'bool true'      => [true, true],
            'bool false'     => [false, false],
            'int 1'          => [1, true],
            'int 0'          => [0, false],
            'int negative'   => [-5, true],
            'string "1"'     => ['1', true],
            'string "0"'     => ['0', false],
            'string true'    => ['true', true],
            'string TRUE'    => ['TRUE', true],
            'string yes'     => ['yes', true],
            'string YES'     => ['YES', true],
            'string false'   => ['false', false],
            'string no'      => ['no', false],
            'empty string'   => ['', false],
            'random string'  => ['banana', false],
            'null'           => [null, false],
            'array'          => [[true], false],
            'float'          => [1.0, false], // not handled — only bool/int/string
        ];
    }

    #[DataProvider('asBoolProvider')]
    public function testAsBool(mixed $input, bool $expected): void
    {
        $this->assertSame($expected, TypeCoerce::asBool($input));
    }

    // ---------------------------------------------------------------
    // asNullableInt()
    // ---------------------------------------------------------------

    /**
     * @return array<string, array{mixed, int|null}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function asNullableIntProvider(): array
    {
        return [
            'null passthrough'       => [null, null],
            'int passthrough'        => [42, 42],
            'zero'                   => [0, 0],
            'negative'               => [-1, -1],
            'numeric string'         => ['123', 123],
            'non-numeric string'     => ['abc', null],
            'empty string'           => ['', null],
            'bool'                   => [true, null],
            'array'                  => [[1], null],
            'float'                  => [3.14, null],
        ];
    }

    #[DataProvider('asNullableIntProvider')]
    public function testAsNullableInt(mixed $input, ?int $expected): void
    {
        $this->assertSame($expected, TypeCoerce::asNullableInt($input));
    }
}
