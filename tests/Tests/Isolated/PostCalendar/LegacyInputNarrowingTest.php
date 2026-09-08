<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar;

use OpenEMR\PostCalendar\LegacyInputNarrowing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('postcalendar')]
final class LegacyInputNarrowingTest extends TestCase
{
    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonArrayInputProvider(): array
    {
        return [
            'null'       => [null],
            'int'        => [42],
            'string'     => ['hello'],
            'bool'       => [true],
            'float'      => [3.14],
            'object'     => [new \stdClass()],
        ];
    }

    /** @return array<string, array{mixed, string}> */
    public static function nonStringInputProvider(): array
    {
        return [
            'null'   => [null, ''],
            'int'    => [42, ''],
            'bool'   => [true, ''],
            'array'  => [[], ''],
            'object' => [new \stdClass(), ''],
        ];
    }

    #[DataProvider('nonArrayInputProvider')]
    public function testRowListReturnsEmptyForNonArrayInputs(mixed $value): void
    {
        self::assertSame([], LegacyInputNarrowing::rowList($value));
    }

    public function testRowListPassesThroughArrayOfAssocArrays(): void
    {
        $input = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ];
        $result = LegacyInputNarrowing::rowList($input);
        self::assertCount(2, $result);
        self::assertSame(['id' => 1, 'name' => 'A'], $result[0]);
        self::assertSame(['id' => 2, 'name' => 'B'], $result[1]);
    }

    public function testRowListDropsNonArrayEntries(): void
    {
        $input = [
            ['id' => 1],
            'not-an-array',
            42,
            ['id' => 2],
            null,
        ];
        $result = LegacyInputNarrowing::rowList($input);
        self::assertCount(2, $result);
        self::assertSame(['id' => 1], $result[0]);
        self::assertSame(['id' => 2], $result[1]);
    }

    public function testRowListPreservesValuesAndStringKeyedFieldsAcrossNarrowing(): void
    {
        // A row mixing int and string keys round-trips by value-count and
        // its named field; the int keys themselves are coerced to string
        // representation by the narrower for the static type but PHP
        // auto-converts numeric strings back to int at storage time, so
        // we don't make a runtime assertion about the int key type.
        $input = [
            [0 => 'first', 1 => 'second', 'name' => 'mixed'],
        ];
        $result = LegacyInputNarrowing::rowList($input);
        self::assertCount(1, $result);
        self::assertCount(3, $result[0]);
        self::assertContains('first', $result[0]);
        self::assertContains('second', $result[0]);
        self::assertSame('mixed', $result[0]['name']);
    }

    #[DataProvider('nonArrayInputProvider')]
    public function testDateEventsReturnsEmptyForNonArrayInputs(mixed $value): void
    {
        self::assertSame([], LegacyInputNarrowing::dateEvents($value));
    }

    public function testDateEventsPassesThroughDateKeyedEventMap(): void
    {
        $input = [
            '2026-03-15' => [
                ['eid' => 1, 'title' => 'Event A'],
                ['eid' => 2, 'title' => 'Event B'],
            ],
            '2026-03-16' => [],
        ];
        $result = LegacyInputNarrowing::dateEvents($input);
        self::assertArrayHasKey('2026-03-15', $result);
        self::assertCount(2, $result['2026-03-15']);
        self::assertSame(1, $result['2026-03-15'][0]['eid']);
        self::assertArrayHasKey('2026-03-16', $result);
        self::assertSame([], $result['2026-03-16']);
    }

    public function testDateEventsDropsNonArrayEventLists(): void
    {
        $input = [
            '2026-03-15' => [['eid' => 1]],
            '2026-03-16' => 'not-an-array',
            '2026-03-17' => null,
        ];
        $result = LegacyInputNarrowing::dateEvents($input);
        self::assertArrayHasKey('2026-03-15', $result);
        self::assertCount(1, $result['2026-03-15']);
        self::assertSame([], $result['2026-03-16']);
        self::assertSame([], $result['2026-03-17']);
    }

    public function testDateEventsPreservesValuesAcrossIntKeyedInput(): void
    {
        // Pathological case — int-keyed map: the narrower doesn't drop
        // entries on the key-type mismatch. (PHP auto-converts the
        // stringified numeric back to int at storage time; we assert
        // count + value preservation rather than key-type details.)
        $input = [
            0 => [['eid' => 1]],
            1 => [['eid' => 2]],
        ];
        $result = LegacyInputNarrowing::dateEvents($input);
        self::assertCount(2, $result);
        $values = array_values($result);
        self::assertSame([['eid' => 1]], $values[0]);
        self::assertSame([['eid' => 2]], $values[1]);
    }

    #[DataProvider('nonArrayInputProvider')]
    public function testTimeRowsReturnsEmptyForNonArrayInputs(mixed $value): void
    {
        self::assertSame([], LegacyInputNarrowing::timeRows($value));
    }

    public function testTimeRowsPassesThroughWellShapedTimeRows(): void
    {
        $input = [
            ['hour' => 8,  'minute' => 0,  'mer' => 'am'],
            ['hour' => 8,  'minute' => 30, 'mer' => 'am'],
            ['hour' => 17, 'minute' => 0,  'mer' => 'pm'],
        ];
        $result = LegacyInputNarrowing::timeRows($input);
        self::assertCount(3, $result);
        self::assertSame(['hour' => 8, 'minute' => 0, 'mer' => 'am'], $result[0]);
        self::assertSame(['hour' => 17, 'minute' => 0, 'mer' => 'pm'], $result[2]);
    }

    public function testTimeRowsOmitsMerWhenMissingOrNonString(): void
    {
        $input = [
            ['hour' => 8, 'minute' => 0],            // mer missing
            ['hour' => 9, 'minute' => 30, 'mer' => 42],  // mer non-string
        ];
        $result = LegacyInputNarrowing::timeRows($input);
        self::assertCount(2, $result);
        self::assertArrayNotHasKey('mer', $result[0]);
        self::assertArrayNotHasKey('mer', $result[1]);
        self::assertSame(['hour' => 8, 'minute' => 0], $result[0]);
        self::assertSame(['hour' => 9, 'minute' => 30], $result[1]);
    }

    public function testTimeRowsDefaultsMissingHourAndMinuteToZero(): void
    {
        $input = [
            ['mer' => 'am'],          // both missing
            ['hour' => true, 'minute' => null],  // wrong types (not int|string)
        ];
        $result = LegacyInputNarrowing::timeRows($input);
        self::assertCount(2, $result);
        self::assertSame(0, $result[0]['hour']);
        self::assertSame(0, $result[0]['minute']);
        self::assertArrayHasKey('mer', $result[0]);
        self::assertSame('am', $result[0]['mer']);
        self::assertSame(0, $result[1]['hour']);
        self::assertSame(0, $result[1]['minute']);
    }

    public function testTimeRowsAcceptsStringTypedHourAndMinute(): void
    {
        // Legacy code stores time components as strings in some paths —
        // the row shape accepts int|string for both.
        $input = [
            ['hour' => '9', 'minute' => '15'],
        ];
        $result = LegacyInputNarrowing::timeRows($input);
        self::assertSame('9', $result[0]['hour']);
        self::assertSame('15', $result[0]['minute']);
    }

    public function testTimeRowsDropsNonArrayEntries(): void
    {
        $input = [
            ['hour' => 8, 'minute' => 0],
            'not-a-row',
            null,
            ['hour' => 9, 'minute' => 0],
        ];
        $result = LegacyInputNarrowing::timeRows($input);
        self::assertCount(2, $result);
    }

    public function testStringValuePassesThroughString(): void
    {
        self::assertSame('hello', LegacyInputNarrowing::stringValue('hello'));
        self::assertSame('', LegacyInputNarrowing::stringValue(''));
    }

    #[DataProvider('nonStringInputProvider')]
    public function testStringValueReturnsDefaultForNonStrings(mixed $value, string $expected): void
    {
        self::assertSame($expected, LegacyInputNarrowing::stringValue($value));
    }

    public function testStringValueUsesProvidedDefault(): void
    {
        self::assertSame('fallback', LegacyInputNarrowing::stringValue(null, 'fallback'));
        self::assertSame('fallback', LegacyInputNarrowing::stringValue(42, 'fallback'));
        // Empty string is a valid string and overrides the default.
        self::assertSame('', LegacyInputNarrowing::stringValue('', 'fallback'));
    }

    #[DataProvider('nonArrayInputProvider')]
    public function testStringListReturnsEmptyForNonArrayInputs(mixed $value): void
    {
        self::assertSame([], LegacyInputNarrowing::stringList($value));
    }

    public function testStringListPassesThroughListOfStrings(): void
    {
        $input = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        self::assertSame($input, LegacyInputNarrowing::stringList($input));
    }

    public function testStringListDropsNonStringEntries(): void
    {
        $input = ['a', 42, 'b', null, 'c', new \stdClass(), 'd'];
        self::assertSame(['a', 'b', 'c', 'd'], LegacyInputNarrowing::stringList($input));
    }

    public function testStringListReturnsEmptyForEmptyArray(): void
    {
        self::assertSame([], LegacyInputNarrowing::stringList([]));
    }

    public function testIntValuePassesThroughInt(): void
    {
        self::assertSame(42, LegacyInputNarrowing::intValue(42));
        self::assertSame(0, LegacyInputNarrowing::intValue(0));
        self::assertSame(-7, LegacyInputNarrowing::intValue(-7));
    }

    public function testIntValueCoercesNonEmptyNumericString(): void
    {
        self::assertSame(30, LegacyInputNarrowing::intValue('30'));
        self::assertSame(-15, LegacyInputNarrowing::intValue('-15'));
    }

    public function testIntValueReturnsDefaultForEmptyStringOrOtherTypes(): void
    {
        self::assertSame(0, LegacyInputNarrowing::intValue(''));
        self::assertSame(0, LegacyInputNarrowing::intValue(null));
        self::assertSame(0, LegacyInputNarrowing::intValue(true));
        self::assertSame(0, LegacyInputNarrowing::intValue([]));
    }

    public function testIntValueUsesProvidedDefault(): void
    {
        self::assertSame(30, LegacyInputNarrowing::intValue(null, 30));
        self::assertSame(30, LegacyInputNarrowing::intValue('', 30));
        // A real int short-circuits the default.
        self::assertSame(99, LegacyInputNarrowing::intValue(99, 30));
    }
}
