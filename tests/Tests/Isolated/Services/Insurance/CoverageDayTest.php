<?php

/**
 * CoverageDay: the day each use asks the coverage service about.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Insurance;

use OpenEMR\Services\Insurance\CoverageDay;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CoverageDayTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function stored(): array
    {
        return [
            'a date' => ['2026-11-04', '2026-11-04'],
            'a datetime: its day' => ['2026-11-04 23:30:00', '2026-11-04'],
            'null' => [null, null],
            'blank' => ['', null],
            'zero' => ['0000-00-00', null],
            'a zero datetime' => ['0000-00-00 00:00:00', null],
            'a zero day' => ['2026-05-00', null],
            'not a day' => ['2026-02-30', null],
            'too short' => ['2026-11', null],
            'not a date' => ['yesterday!', null],
            'not a string: a number' => [20261104, null],
            'not a string: false (a failed fetch)' => [false, null],
            'not a string: an array' => [['2026-11-04'], null],
        ];
    }

    /**
     * A DATE or DATETIME gives its calendar day; blank, zero, partial or impossible dates and non-strings give none.
     */
    #[Test]
    #[DataProvider('stored')]
    public function aStoredValueIsItsCalendarDayOrNothing(mixed $value, ?string $expected): void
    {
        self::assertSame($expected, CoverageDay::fromStored($value)?->format('Y-m-d'));
    }

    /**
     * A DATETIME late in the day gives that day at midnight in PHP's default zone, where the rule's dates are.
     */
    #[Test]
    public function theDayIsMidnightInPhpsZoneAsTheRulesDatesAre(): void
    {
        $day = CoverageDay::fromStored('2026-11-04 23:30:00');

        self::assertNotNull($day);
        self::assertSame('2026-11-04 00:00:00', $day->format('Y-m-d H:i:s'));
        self::assertSame(date_default_timezone_get(), $day->getTimezone()->getName());
    }
}
