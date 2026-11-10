<?php

/**
 * InForce: the one "in force" rule for issues and prescriptions, in PHP and as SQL.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Dates;

use DateTimeImmutable;
use OpenEMR\Common\Dates\Day;
use OpenEMR\Common\Dates\InForce;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InForceTest extends TestCase
{
    private const NOW = '2026-11-04 10:00:00';

    /**
     * @return array<string, array{array<string, mixed>, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function issues(): array
    {
        return [
            'no end' => [['activity' => '1', 'enddate' => null], true],
            'a zero end is no end' => [['activity' => 1, 'enddate' => '0000-00-00 00:00:00'], true],
            'a zero date end is no end' => [['activity' => 1, 'enddate' => '0000-00-00'], true],
            'a blank end is no end' => [['activity' => 1, 'enddate' => ''], true],
            'an end in 30 days' => [['activity' => 1, 'enddate' => '2026-12-04 00:00:00'], true],
            'an end later today' => [['activity' => 1, 'enddate' => '2026-11-04 23:30:00'], true],
            'an end one second on' => [['activity' => 1, 'enddate' => '2026-11-04 10:00:01'], true],
            'an end at this instant: still in force' => [['activity' => 1, 'enddate' => '2026-11-04 10:00:00'], true],
            'an end one second ago' => [['activity' => 1, 'enddate' => '2026-11-04 09:59:59'], false],
            'an end earlier today' => [['activity' => 1, 'enddate' => '2026-11-04 00:00:01'], false],
            'an end at midnight today (the instant it names)' => [['activity' => 1, 'enddate' => '2026-11-04 00:00:00'], false],
            'a DATE-only end today: midnight' => [['activity' => 1, 'enddate' => '2026-11-04'], false],
            'an end 30 days ago' => [['activity' => 1, 'enddate' => '2026-10-05 00:00:00'], false],
            'a zero day end compares as stored (before the month)' => [['activity' => 1, 'enddate' => '2026-11-00 00:00:00'], false],
            'activity 0: withdrawn' => [['activity' => '0', 'enddate' => null], false],
            'activity 0 as an int' => [['activity' => 0, 'enddate' => null], false],
            'activity NULL: not withdrawn' => [['activity' => null, 'enddate' => null], true],
            'no activity column' => [['enddate' => null], true],
            'outcome and occurrence are not read' => [['activity' => 1, 'enddate' => null, 'outcome' => 1, 'occurrence' => 1], true],
            'a future begin date is not read' => [['activity' => 1, 'enddate' => null, 'begdate' => '2027-01-01 00:00:00'], true],
            'verification is not read' => [['activity' => 1, 'enddate' => null, 'verification' => 'entered-in-error'], true],
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    #[Test]
    #[DataProvider('issues')]
    public function anIssueIsInForceUntilItsEndInstantUnlessWithdrawn(array $row, bool $expected): void
    {
        self::assertSame($expected, InForce::issue($row, new DateTimeImmutable(self::NOW)));
    }

    /**
     * @return array<string, array{array<string, mixed>, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function prescriptions(): array
    {
        return [
            'active, no end' => [['active' => '1', 'end_date' => null], true],
            'active as an int' => [['active' => 1, 'end_date' => null], true],
            'a zero end is no end' => [['active' => 1, 'end_date' => '0000-00-00'], true],
            'an end in 30 days' => [['active' => 1, 'end_date' => '2026-12-04'], true],
            'an end today: the day it stopped' => [['active' => 1, 'end_date' => '2026-11-04'], false],
            'an end tomorrow' => [['active' => 1, 'end_date' => '2026-11-05'], true],
            'an end yesterday' => [['active' => 1, 'end_date' => '2026-11-03'], false],
            'active 0 (currently active unticked)' => [['active' => '0', 'end_date' => null], false],
            'active -1 (the C-CDA import discontinued it)' => [['active' => '-1', 'end_date' => '2026-11-04'], false],
            'a future start is not read' => [['active' => 1, 'end_date' => null, 'start_date' => '2027-01-01'], true],
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    #[Test]
    #[DataProvider('prescriptions')]
    public function aPrescriptionIsInForceUntilItsEndDay(array $row, bool $expected): void
    {
        self::assertSame($expected, InForce::prescription($row, Day::of(2026, 11, 4)));
    }

    #[Test]
    public function theSqlFormsBindTheSameMoment(): void
    {
        self::assertSame(
            "((`l`.`activity` IS NULL OR `l`.`activity` <> 0) AND (`l`.`enddate` IS NULL OR `l`.`enddate` = '0000-00-00 00:00:00' OR `l`.`enddate` >= ?))",
            InForce::issueSql('l')
        );
        self::assertSame(['2026-11-04 10:00:00'], InForce::issueBinds(new DateTimeImmutable(self::NOW)));
        self::assertSame(
            "(`active` = 1 AND (`end_date` IS NULL OR `end_date` = '0000-00-00' OR `end_date` > ?))",
            InForce::prescriptionSql()
        );
        self::assertSame(['2026-11-04'], InForce::prescriptionBinds(Day::of(2026, 11, 4)));
    }
}
