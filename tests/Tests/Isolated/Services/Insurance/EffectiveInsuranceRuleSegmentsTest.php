<?php

/**
 * EffectiveInsuranceRule::segments(): the runs of days on which each policy heads a level within a range.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Insurance;

use DateTimeImmutable;
use DateTimeZone;
use OpenEMR\Services\Insurance\CoverageSegment;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;
use OpenEMR\Services\Insurance\InsuranceLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EffectiveInsuranceRuleSegmentsTest extends TestCase
{
    private const FROM = '2025-11-15';
    private const TO = '2026-04-30';

    /**
     * An insurance_data row as a read with bound values returns it: id as an int, the rest as stored.
     *
     * @return array<string, mixed>
     */
    private static function row(int $id, string $type, int|string|null $provider, ?string $start, ?string $end): array
    {
        return ['id' => $id, 'uuid' => null, 'type' => $type, 'provider' => $provider, 'date' => $start, 'date_end' => $end];
    }

    /**
     * @param list<array<mixed>> $rows
     * @return list<string> each run as "first..last #id"
     */
    private static function runs(array $rows, string $from = self::FROM, string $to = self::TO, InsuranceLevel $level = InsuranceLevel::Primary): array
    {
        return array_map(
            static fn(CoverageSegment $s): string => $s->from->format('Y-m-d') . '..' . $s->to->format('Y-m-d') . " #{$s->policy->id}",
            EffectiveInsuranceRule::segments($rows, $level, new DateTimeImmutable($from), new DateTimeImmutable($to))
        );
    }

    /**
     * Arrangements of one patient's rows, each checked day by day against choose().
     *
     * @return array<string, array{list<array<mixed>>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function arrangements(): array
    {
        return [
            'no rows' => [[]],
            'one open policy' => [[self::row(1, 'primary', 101, '2020-01-01', null)]],
            'no dates' => [[self::row(1, 'primary', 101, null, null)]],
            'starts and ends inside the range' => [[self::row(1, 'primary', 101, '2026-03-10', '2026-03-20')]],
            'on the range\'s first and last day' => [[self::row(1, 'primary', 101, self::FROM, self::TO)]],
            'starts the day after the range' => [[self::row(1, 'primary', 101, '2026-05-01', null)]],
            'ended the day before the range' => [[self::row(1, 'primary', 101, '2020-01-01', '2025-11-14')]],
            'an end before its start' => [[self::row(1, 'primary', 101, '2026-03-10', '2026-03-01')]],
            'a change of insurer and back' => [[
                self::row(1, 'primary', 101, '2020-01-01', null),
                self::row(2, 'primary', 102, '2026-02-01', '2026-02-28'),
                self::row(3, 'primary', 101, '2026-03-15', null),
            ]],
            'next year\'s card entered in advance' => [[
                self::row(1, 'primary', 101, '2020-01-01', null),
                self::row(2, 'primary', 102, '2026-01-01', null),
            ]],
            'a newer policy that ends leaves the older one' => [[
                self::row(1, 'primary', 101, '2020-01-01', null),
                self::row(2, 'primary', 102, '2026-01-10', '2026-02-10'),
            ]],
            'an Unassigned row for a month' => [[
                self::row(1, 'primary', 101, '2020-01-01', null),
                self::row(2, 'primary', '', '2026-02-01', '2026-02-28'),
            ]],
            'company 0 from a date on' => [[
                self::row(1, 'primary', 101, '2020-01-01', null),
                self::row(2, 'primary', '0', '2026-02-10', null),
            ]],
            'an outranked policy ends while the newer one holds' => [[
                self::row(1, 'primary', 101, '2026-01-10', null),
                self::row(2, 'primary', 102, '2020-01-01', '2026-02-15'),
            ]],
            'two undated policies' => [[
                self::row(1, 'primary', 101, null, null),
                self::row(2, 'primary', 102, null, null),
            ]],
            'a one-day policy over an open one' => [[
                self::row(1, 'primary', 101, '2020-01-01', null),
                self::row(2, 'primary', 102, '2026-01-15', '2026-01-15'),
            ]],
            'zero and partial-zero dates, compared as stored' => [[
                self::row(1, 'primary', 101, '0000-00-00', '2025-12-00'),
                self::row(2, 'primary', 102, '2026-00-00', '2026-02-00'),
                self::row(3, 'primary', 103, '2026-03-00', '0000-00-00'),
            ]],
            'dates no DATE column holds, compared as strings' => [[
                self::row(1, 'primary', 101, '2025-13-05', '2026-02-30'),
                self::row(2, 'primary', 102, '2026-04-31', null),
            ]],
            'every level, each changing' => [[
                self::row(1, 'primary', 101, '2020-01-01', null),
                self::row(2, 'secondary', 102, '2026-02-01', '2026-02-10'),
                self::row(3, 'tertiary', 103, '2026-03-01', null),
                self::row(4, 'secondary', '', '2026-04-01', null),
            ]],
        ];
    }

    /**
     * @param list<array<mixed>> $rows
     */
    #[Test]
    #[DataProvider('arrangements')]
    public function everyDayOfTheRangeIsInTheRunOfThePolicyChooseGives(array $rows): void
    {
        $first = new DateTimeImmutable(self::FROM);
        $last = new DateTimeImmutable(self::TO);
        foreach (InsuranceLevel::cases() as $level) {
            $heldOn = [];
            $previous = null;
            foreach (EffectiveInsuranceRule::segments($rows, $level, $first, $last) as $segment) {
                self::assertGreaterThanOrEqual(0, $segment->to <=> $segment->from, 'a run ends on or after its first day');
                if ($previous !== null) {
                    self::assertLessThan($segment->from, $previous->to, 'runs come oldest first and don\'t overlap');
                    $touches = $previous->to->modify('+1 day')->format('Y-m-d') === $segment->from->format('Y-m-d');
                    self::assertFalse($touches && $previous->policy->id === $segment->policy->id, 'a run is as long as it can be');
                }
                for ($day = $segment->from; $day <= $segment->to; $day = $day->modify('+1 day')) {
                    $heldOn[$day->format('Y-m-d')] = $segment->policy->id;
                }
                $previous = $segment;
            }
            for ($day = $first; $day <= $last; $day = $day->modify('+1 day')) {
                $chosen = null;
                foreach (EffectiveInsuranceRule::choose($rows, $day) as $policy) {
                    if ($policy->level === $level) {
                        $chosen = $policy->id;
                    }
                }
                self::assertSame($chosen, $heldOn[$day->format('Y-m-d')] ?? null, "{$level->value} on {$day->format('Y-m-d')}");
            }
            self::assertSame([], array_diff(array_keys($heldOn), self::daysOf($first, $last)), 'no run leaves the range');
        }
    }

    /**
     * @return list<string>
     */
    private static function daysOf(DateTimeImmutable $first, DateTimeImmutable $last): array
    {
        $days = [];
        for ($day = $first; $day <= $last; $day = $day->modify('+1 day')) {
            $days[] = $day->format('Y-m-d');
        }
        return $days;
    }

    #[Test]
    public function aRunIsCutToTheRangeAndThePolicyKeepsItsOwnDates(): void
    {
        $segments = EffectiveInsuranceRule::segments(
            [self::row(1, 'primary', 101, '2020-01-01', null)],
            InsuranceLevel::Primary,
            new DateTimeImmutable('2026-01-01'),
            new DateTimeImmutable('2026-12-31')
        );

        self::assertCount(1, $segments);
        self::assertSame('2026-01-01', $segments[0]->from->format('Y-m-d'));
        self::assertSame('2026-12-31', $segments[0]->to->format('Y-m-d'));
        self::assertSame(101, $segments[0]->policy->companyId);
        self::assertSame('2020-01-01', $segments[0]->policy->start?->format('Y-m-d'));
        self::assertNull($segments[0]->policy->end);
    }

    #[Test]
    public function aChangeOfInsurerAndBackGivesThreeRuns(): void
    {
        $rows = [
            self::row(1, 'primary', 101, '2020-01-01', null),
            self::row(2, 'primary', 102, '2026-02-01', '2026-02-28'),
        ];

        self::assertSame(
            ['2026-01-01..2026-01-31 #1', '2026-02-01..2026-02-28 #2', '2026-03-01..2026-04-30 #1'],
            self::runs($rows, '2026-01-01')
        );
    }

    #[Test]
    public function aMonthUnassignedLeavesAGap(): void
    {
        $rows = [
            self::row(1, 'primary', 101, '2020-01-01', null),
            self::row(2, 'primary', '', '2026-02-01', '2026-02-28'),
        ];

        self::assertSame(['2026-01-01..2026-01-31 #1', '2026-03-01..2026-04-30 #1'], self::runs($rows, '2026-01-01'));
    }

    #[Test]
    public function aChangeAtAnotherLevelDoesNotSplitARun(): void
    {
        $rows = [
            self::row(1, 'primary', 101, '2020-01-01', null),
            self::row(2, 'secondary', 102, '2026-02-01', '2026-02-10'),
        ];

        self::assertSame(['2026-01-01..2026-04-30 #1'], self::runs($rows, '2026-01-01'));
        self::assertSame(['2026-02-01..2026-02-10 #2'], self::runs($rows, '2026-01-01', self::TO, InsuranceLevel::Secondary));
        self::assertSame([], self::runs($rows, '2026-01-01', self::TO, InsuranceLevel::Tertiary));
    }

    #[Test]
    public function datesWithAZeroDayOrMonthChangeTheAnswerWhereSqlWould(): void
    {
        $rows = [
            self::row(1, 'primary', 101, '2026-00-00', '2026-02-00'),
            self::row(2, 'primary', 102, '2026-03-00', null),
        ];

        // 2026-00-00 sorts before 2026-01-01, 2026-02-00 before 2026-02-01, 2026-03-00 before 2026-03-01.
        self::assertSame(['2026-01-01..2026-01-31 #1', '2026-03-01..2026-04-30 #2'], self::runs($rows));
    }

    #[Test]
    public function anEmptyRangeOrNoRowsHasNoRuns(): void
    {
        $rows = [self::row(1, 'primary', 101, null, null)];

        self::assertSame([], self::runs($rows, '2026-02-01', '2026-01-31'));
        self::assertSame([], self::runs([]));
        self::assertSame(['2026-02-01..2026-02-01 #1'], self::runs($rows, '2026-02-01', '2026-02-01'));
    }

    #[Test]
    public function theRangeIsCalendarDaysWhateverTheTimeOrZone(): void
    {
        $segments = EffectiveInsuranceRule::segments(
            [self::row(1, 'primary', 101, '2026-01-01', '2026-01-31')],
            InsuranceLevel::Primary,
            new DateTimeImmutable('2026-01-01 23:59:59', new DateTimeZone('UTC')),
            // Still the 30th in UTC, but the 31st where the date was given.
            new DateTimeImmutable('2026-01-31 01:00:00', new DateTimeZone('Pacific/Kiritimati'))
        );

        self::assertCount(1, $segments);
        self::assertSame('2026-01-01', $segments[0]->from->format('Y-m-d'));
        self::assertSame('2026-01-31', $segments[0]->to->format('Y-m-d'));
    }
}
