<?php

/**
 * Isolated MetricCount Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Reports;

use OpenEMR\Reports\MetricCount;
use PHPUnit\Framework\TestCase;

final class MetricCountTest extends TestCase
{
    public function testReadsTheNamedColumn(): void
    {
        self::assertSame(12, MetricCount::fromColumn(['count' => '12'], 'count'));
    }

    public function testAMissingColumnIsZero(): void
    {
        self::assertSame(0, MetricCount::fromColumn(['other' => '12'], 'count'));
    }

    public function testNoRowIsZero(): void
    {
        // querySingleRow() returns false when the query matches nothing.
        self::assertSame(0, MetricCount::fromColumn(false, 'count'));
        self::assertSame(0, MetricCount::fromColumn(null, 'count'));
        self::assertSame(0, MetricCount::fromColumn([], 'count'));
    }

    public function testSumOverNoRowsIsNullAndReadsAsZero(): void
    {
        self::assertSame(0, MetricCount::fromColumn(['count' => null], 'count'));
    }

    public function testRejectsAValueThatIsNotACount(): void
    {
        // A count cannot be negative or fractional, so these are not coerced.
        // Both forms matter: the driver returns strings, but a caller may pass
        // an int, and the two branches must agree.
        self::assertSame(0, MetricCount::fromValue(-5));
        self::assertSame(0, MetricCount::fromValue(PHP_INT_MIN));
        self::assertSame(0, MetricCount::fromValue('-5'));
        self::assertSame(0, MetricCount::fromValue('2.5'));
        self::assertSame(0, MetricCount::fromValue('twelve'));
        self::assertSame(0, MetricCount::fromValue(true));
        self::assertSame(0, MetricCount::fromValue([1]));
        self::assertSame(0, MetricCount::fromValue(new \stdClass()));
    }

    public function testAcceptsTheFormsTheDriverActuallyReturns(): void
    {
        self::assertSame(0, MetricCount::fromValue('0'));
        self::assertSame(7, MetricCount::fromValue('7'));
        self::assertSame(7, MetricCount::fromValue(7));
        self::assertSame(0, MetricCount::fromValue(0));
    }
}
