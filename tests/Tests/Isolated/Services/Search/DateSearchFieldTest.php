<?php

/**
 * DateSearchField Unit Tests
 *
 * Pins which FHIR date search values DateSearchField accepts and the range
 * that each value covers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    oldrobotdev <hi@oldrobot.us>
 * @copyright Copyright (c) 2026 oldrobotdev <hi@oldrobot.us>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\SearchComparator;
use OpenEMR\Services\Search\SearchFieldComparableValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateSearchFieldTest extends TestCase
{
    private const RANGE_FORMAT = 'Y-m-d\TH:i:s.v';

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function precisionProvider(): array
    {
        return [
            'year'                   => ['2021', '2021-01-01T00:00:00.000', '2021-12-31T23:59:59.999'],
            'month'                  => ['2021-02', '2021-02-01T00:00:00.000', '2021-02-28T23:59:59.999'],
            'day'                    => ['2021-03-04', '2021-03-04T00:00:00.000', '2021-03-04T23:59:59.999'],
            'minute'                 => ['2021-03-04T10:30', '2021-03-04T10:30:00.000', '2021-03-04T10:30:59.999'],
            'minute with utc'        => ['2021-03-04T10:30Z', '2021-03-04T10:30:00.000', '2021-03-04T10:30:59.999'],
            'minute with utc offset' => ['2021-03-04T10:30+02:00', '2021-03-04T10:30:00.000', '2021-03-04T10:30:59.999'],
            'second'                 => ['2021-03-04T10:30:15', '2021-03-04T10:30:15.000', '2021-03-04T10:30:15.999'],
            'tenth of a second'      => ['2021-03-04T10:30:15.5Z', '2021-03-04T10:30:15.500', '2021-03-04T10:30:15.599'],
            'hundredth of a second'  => ['2021-03-04T10:30:15.25Z', '2021-03-04T10:30:15.250', '2021-03-04T10:30:15.259'],
            'millisecond'            => ['2021-03-04T10:30:15.250Z', '2021-03-04T10:30:15.250', '2021-03-04T10:30:15.250'],
            'nanosecond'             => ['2021-03-04T10:30:15.123456789Z', '2021-03-04T10:30:15.123', '2021-03-04T10:30:15.123'],
        ];
    }

    #[DataProvider('precisionProvider')]
    public function testValueCoversThePeriodOfItsPrecision(string $value, string $start, string $end): void
    {
        $period = $this->parse($value)->getValue();

        $this->assertInstanceOf(\DatePeriod::class, $period);
        $this->assertSame($start, $period->getStartDate()->format(self::RANGE_FORMAT));
        $this->assertSame($end, $period->getEndDate()?->format(self::RANGE_FORMAT));
    }

    public function testComparatorIsReadFromMinutePrecisionValue(): void
    {
        $this->assertSame(
            SearchComparator::GREATER_THAN_OR_EQUAL_TO,
            $this->parse('ge2021-03-04T10:30')->getComparator()
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidValueProvider(): array
    {
        return [
            'hour without minutes'   => ['2021-03-04T10'],
            'fraction after minutes' => ['2021-03-04T10:30.5'],
            'fraction after day'     => ['2021-03-04.5'],
        ];
    }

    #[DataProvider('invalidValueProvider')]
    public function testInvalidValueIsRejected(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DateSearchField('date', [$value]);
    }

    private function parse(string $value): SearchFieldComparableValue
    {
        return (new DateSearchField('date', [$value]))->getValues()[0];
    }
}
