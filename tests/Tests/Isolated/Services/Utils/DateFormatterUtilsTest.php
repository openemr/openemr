<?php

/**
 * Isolated DateFormatterUtils Test
 *
 * Tests date and time formatting with injectable globals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\Utils;

use OpenEMR\Services\Utils\DateFormatterUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class DateFormatterUtilsTest extends TestCase
{
    private function createGlobals(int $dateFormat = 0, int $timeFormat = 0): ParameterBag
    {
        return new ParameterBag([
            'date_display_format' => $dateFormat,
            'time_display_format' => $timeFormat,
        ]);
    }

    // ==========================================================================
    // isNotEmptyDateTimeString tests
    // ==========================================================================

    public function testIsNotEmptyDateTimeStringWithValidDate(): void
    {
        $this->assertTrue(DateFormatterUtils::isNotEmptyDateTimeString('2024-06-15 14:30:00'));
    }

    public function testIsNotEmptyDateTimeStringWithNull(): void
    {
        $this->assertFalse(DateFormatterUtils::isNotEmptyDateTimeString(null));
    }

    public function testIsNotEmptyDateTimeStringWithEmptyString(): void
    {
        $this->assertFalse(DateFormatterUtils::isNotEmptyDateTimeString(''));
    }

    public function testIsNotEmptyDateTimeStringWithZeroDate(): void
    {
        $this->assertFalse(DateFormatterUtils::isNotEmptyDateTimeString('0000-00-00 00:00:00'));
    }

    public function testIsNotEmptyDateTimeStringWithEpochDate(): void
    {
        $this->assertFalse(DateFormatterUtils::isNotEmptyDateTimeString('1970-01-01 00:00:00'));
    }

    // ==========================================================================
    // DateToYYYYMMDD tests
    // ==========================================================================

    public function testDateToYYYYMMDDWithIsoFormat(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_ISO);

        $result = DateFormatterUtils::DateToYYYYMMDD('2024-06-15', $globals);

        $this->assertSame('2024-06-15', $result);
    }

    public function testDateToYYYYMMDDWithUsFormat(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_US);

        $result = DateFormatterUtils::DateToYYYYMMDD('06/15/2024', $globals);

        $this->assertSame('2024-06-15', $result);
    }

    public function testDateToYYYYMMDDWithIntlFormat(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_INTL);

        $result = DateFormatterUtils::DateToYYYYMMDD('15/06/2024', $globals);

        $this->assertSame('2024-06-15', $result);
    }

    public function testDateToYYYYMMDDWithEmptyString(): void
    {
        $globals = $this->createGlobals();

        $result = DateFormatterUtils::DateToYYYYMMDD('', $globals);

        $this->assertSame('', $result);
    }

    public function testDateToYYYYMMDDWithNull(): void
    {
        $globals = $this->createGlobals();

        $result = DateFormatterUtils::DateToYYYYMMDD(null, $globals);

        $this->assertSame('', $result);
    }

    public function testDateToYYYYMMDDWithWhitespace(): void
    {
        $globals = $this->createGlobals();

        $result = DateFormatterUtils::DateToYYYYMMDD('   ', $globals);

        $this->assertSame('', $result);
    }

    // ==========================================================================
    // dateStringToDateTime tests
    // ==========================================================================

    public function testDateStringToDateTimeWithIsoFormat(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_ISO);

        $result = DateFormatterUtils::dateStringToDateTime('2024-06-15', false, $globals);

        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertSame('2024-06-15', $result->format('Y-m-d'));
    }

    public function testDateStringToDateTimeWithUsFormat(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_US);

        $result = DateFormatterUtils::dateStringToDateTime('06/15/2024', false, $globals);

        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertSame('2024-06-15', $result->format('Y-m-d'));
    }

    public function testDateStringToDateTimeWithIntlFormat(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_INTL);

        $result = DateFormatterUtils::dateStringToDateTime('15/06/2024', false, $globals);

        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertSame('2024-06-15', $result->format('Y-m-d'));
    }

    public function testDateStringToDateTimeWithEmptyStringReturnsCurrentDate(): void
    {
        $globals = $this->createGlobals();

        $result = DateFormatterUtils::dateStringToDateTime('', false, $globals);

        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertSame(date('Y-m-d'), $result->format('Y-m-d'));
    }

    public function testDateStringToDateTimeWithTimeComponent(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_ISO, DateFormatterUtils::TIME_FORMAT_24HR);

        $result = DateFormatterUtils::dateStringToDateTime('2024-06-15 14:30', false, $globals);

        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertSame('2024-06-15 14:30', $result->format('Y-m-d H:i'));
    }

    // ==========================================================================
    // getShortDateFormat tests
    // ==========================================================================

    public function testGetShortDateFormatWithIso(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_ISO);

        $result = DateFormatterUtils::getShortDateFormat(true, $globals);

        $this->assertSame('Y-m-d', $result);
    }

    public function testGetShortDateFormatWithUs(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_US);

        $result = DateFormatterUtils::getShortDateFormat(true, $globals);

        $this->assertSame('m/d/Y', $result);
    }

    public function testGetShortDateFormatWithIntl(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_INTL);

        $result = DateFormatterUtils::getShortDateFormat(true, $globals);

        $this->assertSame('d/m/Y', $result);
    }

    // ==========================================================================
    // oeFormatTime tests
    // ==========================================================================

    public function testOeFormatTimeWith24HourFormat(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_24HR);

        $result = DateFormatterUtils::oeFormatTime('14:30:00', 'global', false, $globals);

        $this->assertSame('14:30', $result);
    }

    public function testOeFormatTimeWith12HourFormat(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_12HR);

        $result = DateFormatterUtils::oeFormatTime('14:30:00', 'global', false, $globals);

        $this->assertSame('2:30 pm', $result);
    }

    public function testOeFormatTimeWith24HourFormatAndSeconds(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_24HR);

        $result = DateFormatterUtils::oeFormatTime('14:30:45', 'global', true, $globals);

        $this->assertSame('14:30:45', $result);
    }

    public function testOeFormatTimeWith12HourFormatAndSeconds(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_12HR);

        $result = DateFormatterUtils::oeFormatTime('14:30:45', 'global', true, $globals);

        $this->assertSame('2:30:45 pm', $result);
    }

    public function testOeFormatTimeWithEmptyTime(): void
    {
        $globals = $this->createGlobals();

        $result = DateFormatterUtils::oeFormatTime('', 'global', false, $globals);

        $this->assertSame('', $result);
    }

    public function testOeFormatTimeWithNullTime(): void
    {
        $globals = $this->createGlobals();

        $result = DateFormatterUtils::oeFormatTime(null, 'global', false, $globals);

        $this->assertSame('', $result);
    }

    public function testOeFormatTimeWithExplicitFormat(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_24HR);

        // Use explicit 12hr format even though global is 24hr
        $result = DateFormatterUtils::oeFormatTime('14:30:00', 1, false, $globals);

        $this->assertSame('2:30 pm', $result);
    }

    public function testOeFormatTimeMorningAm(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_12HR);

        $result = DateFormatterUtils::oeFormatTime('09:15:00', 'global', false, $globals);

        $this->assertSame('9:15 am', $result);
    }

    public function testOeFormatTimeMidnight(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_12HR);

        $result = DateFormatterUtils::oeFormatTime('00:00:00', 'global', false, $globals);

        $this->assertSame('12:00 am', $result);
    }

    public function testOeFormatTimeNoon(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_12HR);

        $result = DateFormatterUtils::oeFormatTime('12:00:00', 'global', false, $globals);

        $this->assertSame('12:00 pm', $result);
    }

    // ==========================================================================
    // oeFormatShortDate tests
    // ==========================================================================

    public function testOeFormatShortDateWithIsoFormat(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_ISO);

        $result = DateFormatterUtils::oeFormatShortDate('2024-06-15', true, $globals);

        $this->assertSame('2024-06-15', $result);
    }

    public function testOeFormatShortDateWithUsFormat(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_US);

        $result = DateFormatterUtils::oeFormatShortDate('2024-06-15', true, $globals);

        $this->assertSame('06/15/2024', $result);
    }

    public function testOeFormatShortDateWithIntlFormat(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_INTL);

        $result = DateFormatterUtils::oeFormatShortDate('2024-06-15', true, $globals);

        $this->assertSame('15/06/2024', $result);
    }

    public function testOeFormatShortDateWithoutYear(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_US);

        $result = DateFormatterUtils::oeFormatShortDate('2024-06-15', false, $globals);

        $this->assertSame('06/15', $result);
    }

    public function testOeFormatShortDateWithIsoFormatWithoutYear(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_ISO);

        $result = DateFormatterUtils::oeFormatShortDate('2024-06-15', false, $globals);

        $this->assertSame('06-15', $result);
    }

    public function testOeFormatShortDateWithIntlFormatWithoutYear(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_INTL);

        $result = DateFormatterUtils::oeFormatShortDate('2024-06-15', false, $globals);

        $this->assertSame('15/06', $result);
    }

    public function testOeFormatShortDateWithShortInput(): void
    {
        $globals = $this->createGlobals();

        $result = DateFormatterUtils::oeFormatShortDate('2024-06', true, $globals);

        $this->assertSame('2024-06', $result);
    }

    // ==========================================================================
    // oeFormatDateTime tests
    // ==========================================================================

    public function testOeFormatDateTimeWithIsoAnd24Hr(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_ISO, DateFormatterUtils::TIME_FORMAT_24HR);

        $result = DateFormatterUtils::oeFormatDateTime('2024-06-15 14:30:00', 'global', false, $globals);

        $this->assertSame('2024-06-15 14:30', $result);
    }

    public function testOeFormatDateTimeWithUsAnd12Hr(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_US, DateFormatterUtils::TIME_FORMAT_12HR);

        $result = DateFormatterUtils::oeFormatDateTime('2024-06-15 14:30:00', 'global', false, $globals);

        $this->assertSame('06/15/2024 2:30 pm', $result);
    }

    public function testOeFormatDateTimeWithSeconds(): void
    {
        $globals = $this->createGlobals(DateFormatterUtils::DATE_FORMAT_ISO, DateFormatterUtils::TIME_FORMAT_24HR);

        $result = DateFormatterUtils::oeFormatDateTime('2024-06-15 14:30:45', 'global', true, $globals);

        $this->assertSame('2024-06-15 14:30:45', $result);
    }

    // ==========================================================================
    // getTimeFormat tests
    // ==========================================================================

    public function testGetTimeFormatWith24Hr(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_24HR);

        $result = DateFormatterUtils::getTimeFormat(false, $globals);

        $this->assertSame('H:i', $result);
    }

    public function testGetTimeFormatWith12Hr(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_12HR);

        $result = DateFormatterUtils::getTimeFormat(false, $globals);

        $this->assertSame('g:i a', $result);
    }

    public function testGetTimeFormatWith24HrAndSeconds(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_24HR);

        $result = DateFormatterUtils::getTimeFormat(true, $globals);

        $this->assertSame('H:i:s', $result);
    }

    public function testGetTimeFormatWith12HrAndSeconds(): void
    {
        $globals = $this->createGlobals(0, DateFormatterUtils::TIME_FORMAT_12HR);

        $result = DateFormatterUtils::getTimeFormat(true, $globals);

        $this->assertSame('g:i:s a', $result);
    }

    // ==========================================================================
    // getFormattedISO8601DateFromDateTime tests
    // ==========================================================================

    public function testGetFormattedISO8601DateFromDateTime(): void
    {
        $dateTime = new \DateTime('2024-06-15 14:30:45.123456', new \DateTimeZone('UTC'));

        $result = DateFormatterUtils::getFormattedISO8601DateFromDateTime($dateTime);

        $this->assertStringStartsWith('2024-06-15T14:30:45.123', $result);
        $this->assertStringEndsWith('+00:00', $result);
    }

    public function testGetFormattedISO8601DateFromDateTimeWithTimezone(): void
    {
        $dateTime = new \DateTime('2024-06-15 14:30:45', new \DateTimeZone('America/New_York'));

        $result = DateFormatterUtils::getFormattedISO8601DateFromDateTime($dateTime);

        $this->assertStringContainsString('2024-06-15T14:30:45', $result);
        // EDT is -04:00, EST is -05:00
        $this->assertMatchesRegularExpression('/-0[45]:00$/', $result);
    }

}
