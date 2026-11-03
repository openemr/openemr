<?php

/**
 * Isolated FhirDateTimeParser Test
 *
 * Covers FHIR R4 date/dateTime parsing on the FHIR write path: precision
 * handling, calendar validation, timezone normalization and the fail-loud
 * contract that turns bad client input into a 400 rather than a dropped
 * element.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\Services\FHIR\FhirDateTimeParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirDateTimeParserTest extends TestCase
{
    private string $originalTimezone;

    protected function setUp(): void
    {
        $this->originalTimezone = date_default_timezone_get();
        // Fixed, DST-observing zone so offset conversions are deterministic.
        date_default_timezone_set('America/New_York');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
    }

    public function testAbsentValuesReturnNull(): void
    {
        $this->assertNull(FhirDateTimeParser::toDbDate(null, 'Condition.onsetDateTime'));
        $this->assertNull(FhirDateTimeParser::toDbDate('', 'Condition.onsetDateTime'));
        $this->assertNull(FhirDateTimeParser::toDbDate('   ', 'Condition.onsetDateTime'));
        $this->assertNull(FhirDateTimeParser::toDbDateTime(null, 'Encounter.period.start'));
    }

    public function testFullDateIsPassedThrough(): void
    {
        $this->assertSame('2024-03-15', FhirDateTimeParser::toDbDate('2024-03-15', 'Condition.onsetDateTime'));
        $this->assertSame(
            '2024-03-15 00:00:00',
            FhirDateTimeParser::toDbDateTime('2024-03-15', 'Condition.onsetDateTime')
        );
    }

    public function testLeapDayIsAccepted(): void
    {
        $this->assertSame('2024-02-29', FhirDateTimeParser::toDbDate('2024-02-29', 'Goal.startDate'));
    }

    /**
     * An offset-bearing dateTime must be converted to server-local wall clock,
     * not formatted in the sender's offset.
     */
    #[DataProvider('offsetProvider')]
    public function testOffsetsAreConvertedToServerTimezone(string $input, string $expected): void
    {
        $this->assertSame($expected, FhirDateTimeParser::toDbDateTime($input, 'Encounter.period.start'));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function offsetProvider(): array
    {
        return [
            'utc' => ['2024-03-15T10:30:00Z', '2024-03-15 06:30:00'],
            'positive offset' => ['2024-03-15T10:30:00+05:00', '2024-03-15 01:30:00'],
            'negative offset' => ['2024-03-15T10:30:00-08:00', '2024-03-15 14:30:00'],
            'fractional seconds' => ['2024-03-15T10:30:00.123Z', '2024-03-15 06:30:00'],
        ];
    }

    public function testTimezoneConversionCanShiftTheCalendarDay(): void
    {
        // 01:30 UTC is still the previous day in America/New_York.
        $this->assertSame(
            '2024-03-14',
            FhirDateTimeParser::toDbDate('2024-03-15T01:30:00Z', 'Immunization.occurrenceDateTime')
        );
    }

    /**
     * FHIR R4 permits YYYY and YYYY-MM. PHP would read "2024" as a clock time
     * (20:24 today), so partial precision must be rejected unless the call site
     * opts in.
     */
    #[DataProvider('partialPrecisionProvider')]
    public function testPartialPrecisionIsRejectedByDefault(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FhirDateTimeParser::toDbDate($input, 'Condition.onsetDateTime');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function partialPrecisionProvider(): array
    {
        return [
            'year only' => ['2024'],
            'year and month' => ['2024-03'],
        ];
    }

    public function testPartialPrecisionWidensToStartOfPeriodWhenAllowed(): void
    {
        $this->assertSame('2024-01-01', FhirDateTimeParser::toDbDate('2024', 'Goal.startDate', true));
        $this->assertSame('2024-03-01', FhirDateTimeParser::toDbDate('2024-03', 'Goal.startDate', true));
    }

    #[DataProvider('invalidValueProvider')]
    public function testInvalidValuesThrow(mixed $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FhirDateTimeParser::toDbDate($input, 'Condition.onsetDateTime');
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidValueProvider(): array
    {
        return [
            'impossible day' => ['2024-02-30'],
            'non-leap february' => ['2023-02-29'],
            'zero day' => ['2024-03-00'],
            'month out of range' => ['2024-13-01'],
            'no timezone on a time' => ['2024-03-15T10:30:00'],
            'missing seconds' => ['2024-03-15T10:30Z'],
            'compact form' => ['20240315'],
            'us format' => ['03/15/2024'],
            'free text' => ['not-a-date'],
            'year below database range' => ['0999-01-01'],
            'integer' => [2024],
            'array' => [['2024-03-15']],
            'boolean' => [true],
        ];
    }

    public function testExceptionMessageNamesTheFhirElement(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Condition\.onsetDateTime/');
        FhirDateTimeParser::toDbDate('2024', 'Condition.onsetDateTime');
    }

    #[DataProvider('precisionProvider')]
    public function testPrecisionDetection(string $input, ?string $expected): void
    {
        $this->assertSame($expected, FhirDateTimeParser::precision($input));
    }

    /**
     * @return array<string, array{0: string, 1: ?string}>
     */
    public static function precisionProvider(): array
    {
        return [
            'year' => ['2024', FhirDateTimeParser::PRECISION_YEAR],
            'month' => ['2024-03', FhirDateTimeParser::PRECISION_MONTH],
            'day' => ['2024-03-15', FhirDateTimeParser::PRECISION_DAY],
            'time' => ['2024-03-15T10:30:00Z', FhirDateTimeParser::PRECISION_TIME],
            'invalid' => ['nope', null],
        ];
    }

    public function testIsValid(): void
    {
        $this->assertTrue(FhirDateTimeParser::isValid('2024-03-15'));
        $this->assertTrue(FhirDateTimeParser::isValid('2024'));
        $this->assertFalse(FhirDateTimeParser::isValid('2024-02-30'));
        $this->assertFalse(FhirDateTimeParser::isValid('0999-01-01'));
        $this->assertFalse(FhirDateTimeParser::isValid(''));
    }

    public function testToDateTimeImmutableReturnsServerTimezone(): void
    {
        $parsed = FhirDateTimeParser::toDateTimeImmutable('2024-03-15T10:30:00Z', 'Encounter.period.start');
        $this->assertInstanceOf(\DateTimeImmutable::class, $parsed);
        $this->assertSame('America/New_York', $parsed->getTimezone()->getName());
    }
}
