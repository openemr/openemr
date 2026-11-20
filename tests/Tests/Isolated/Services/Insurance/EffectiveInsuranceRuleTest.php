<?php

/**
 * EffectiveInsuranceRule: which policy is in force at each level on a day.
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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Insurance\EffectiveInsurance;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;
use OpenEMR\Services\Insurance\InsuranceLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EffectiveInsuranceRuleTest extends TestCase
{
    private const DAY = '2026-11-04';

    /**
     * An insurance_data row as a read with bound values returns it: id as an int, the rest as stored.
     *
     * @return array<string, mixed>
     */
    private static function row(int|string $id, ?string $type, int|string|null $provider, ?string $start, ?string $end): array
    {
        return ['id' => $id, 'uuid' => null, 'type' => $type, 'provider' => $provider, 'date' => $start, 'date_end' => $end];
    }

    /**
     * @param list<array<mixed>> $rows
     * @return list<string> each policy in force as "level #id company"
     */
    private static function chosen(array $rows, string $day = self::DAY): array
    {
        return array_map(
            static fn(EffectiveInsurance $p): string => "{$p->level->value} #{$p->id} company {$p->companyId}",
            EffectiveInsuranceRule::choose($rows, new DateTimeImmutable($day))
        );
    }

    /**
     * @return array<string, array{list<array<mixed>>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function boundaries(): array
    {
        return [
            'no dates: always in force' => [[self::row(1, 'primary', 101, null, null)], ['primary #1 company 101']],
            'in force on its first day' => [[self::row(1, 'primary', 101, '2026-11-04', null)], ['primary #1 company 101']],
            'not yet in force the day before it starts' => [[self::row(1, 'primary', 101, '2026-11-05', null)], []],
            'in force on its last day' => [[self::row(1, 'primary', 101, '2020-01-01', '2026-11-04')], ['primary #1 company 101']],
            'not in force the day after it ends' => [[self::row(1, 'primary', 101, '2020-01-01', '2026-11-03')], []],
            'a one-day policy' => [[self::row(1, 'primary', 101, '2026-11-04', '2026-11-04')], ['primary #1 company 101']],
            'an end before its start: never in force' => [[self::row(1, 'primary', 101, '2026-11-10', '2026-11-01')], []],
            'a blank start means always' => [[self::row(1, 'primary', 101, '', '2026-12-31')], ['primary #1 company 101']],
            'a zero start means always' => [[self::row(1, 'primary', 101, '0000-00-00', null)], ['primary #1 company 101']],
            'a blank end means open' => [[self::row(1, 'primary', 101, '2020-01-01', '')], ['primary #1 company 101']],
            'a zero end means open' => [[self::row(1, 'primary', 101, '2020-01-01', '0000-00-00')], ['primary #1 company 101']],
            'an end with a zero day is compared as stored' => [[self::row(1, 'primary', 101, '2020-01-01', '2026-05-00')], []],
            'a start with a zero day is compared as stored' => [[self::row(1, 'primary', 101, '2026-12-00', null)], []],
        ];
    }

    /**
     * @param list<array<mixed>> $rows
     * @param list<string> $expected
     */
    #[Test]
    #[DataProvider('boundaries')]
    public function bothEndsAreInclusiveAndBlankOrZeroDatesAreOpen(array $rows, array $expected): void
    {
        self::assertSame($expected, self::chosen($rows));
    }

    /**
     * @return array<string, array{list<array<mixed>>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function oneLevel(): array
    {
        return [
            'the latest start in force wins' => [
                [self::row(1, 'primary', 101, '2024-01-01', null), self::row(2, 'primary', 102, '2026-01-01', null)],
                ['primary #2 company 102'],
            ],
            'next year\'s card, entered in advance, waits for its start' => [
                [self::row(1, 'primary', 101, '2024-01-01', null), self::row(2, 'primary', 102, '2027-01-01', null)],
                ['primary #1 company 101'],
            ],
            'a newer policy that has ended leaves the older open one in force' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', 102, '2025-01-01', '2026-06-30')],
                ['primary #1 company 101'],
            ],
            'a dated start beats a blank one entered later' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', 102, null, null)],
                ['primary #1 company 101'],
            ],
            'a dated start beats a blank one entered earlier' => [
                [self::row(1, 'primary', 101, null, null), self::row(2, 'primary', 102, '2020-01-01', null)],
                ['primary #2 company 102'],
            ],
            'a 0000-00-00 start beats a blank one, as SQL sorts them' => [
                [self::row(5, 'primary', 101, '0000-00-00', null), self::row(9, 'primary', 102, null, null)],
                ['primary #5 company 101'],
            ],
            'two undated policies: the later entry wins' => [
                [self::row(7, 'primary', 102, null, null), self::row(3, 'primary', 101, null, null)],
                ['primary #7 company 102'],
            ],
            'a newest row with no company ends the level' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', '', '2026-07-01', null)],
                [],
            ],
            'portal registration\'s company 0 is no company' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', '0', '2026-07-01', null)],
                [],
            ],
            'a NULL company is no company' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', null, '2026-07-01', null)],
                [],
            ],
            'a company that isn\'t an id is no company' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', 'Acme', '2026-07-01', null)],
                [],
            ],
            // A cast would read these as companies 12, 1000 and 0 (none), and MySQL would compare '12abc' as 12.
            'a company with letters after its digits is no company' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', '12abc', '2026-07-01', null)],
                [],
            ],
            'a company in exponent form is no company' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', '1e3', '2026-07-01', null)],
                [],
            ],
            'a company of zeros is no company' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', '00', '2026-07-01', null)],
                [],
            ],
            'a company of 0 and a space is no company' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', '0 ', '2026-07-01', null)],
                [],
            ],
            'a company of spaces is no company' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', '  ', '2026-07-01', null)],
                [],
            ],
            'an older row with no company doesn\'t hide a newer policy' => [
                [self::row(1, 'primary', '', null, null), self::row(2, 'primary', 101, '2024-01-01', null)],
                ['primary #2 company 101'],
            ],
            'an ended no-company row gives the level back to the older policy' => [
                [self::row(1, 'primary', 101, '2020-01-01', null), self::row(2, 'primary', '', '2026-07-01', '2026-08-31')],
                ['primary #1 company 101'],
            ],
        ];
    }

    /**
     * @param list<array<mixed>> $rows
     * @param list<string> $expected
     */
    #[Test]
    #[DataProvider('oneLevel')]
    public function theLatestStartInForceWinsAndNoCompanyMeansNoCoverage(array $rows, array $expected): void
    {
        self::assertSame($expected, self::chosen($rows));
        self::assertSame($expected, self::chosen(array_reverse($rows)), 'the order rows arrive in decides nothing');
    }

    /**
     * Levels are independent: the secondary comes back alone after the primary ends.
     */
    #[Test]
    public function anEndedPrimaryDoesNotHideACurrentSecondary(): void
    {
        $rows = [
            self::row(1, 'primary', 101, '2020-01-01', '2026-07-01'),
            self::row(2, 'secondary', 104, '2020-01-01', null),
        ];

        self::assertSame(['secondary #2 company 104'], self::chosen($rows));
    }

    /**
     * An "Unassigned" primary empties its own level, not the levels after it.
     */
    #[Test]
    public function aPrimaryWithNoCompanyDoesNotHideACurrentSecondary(): void
    {
        $rows = [
            self::row(1, 'primary', '', null, null),
            self::row(2, 'secondary', 104, '2020-01-01', null),
        ];

        self::assertSame(['secondary #2 company 104'], self::chosen($rows));
    }

    /**
     * Primary, secondary, tertiary whatever order the rows arrive in; an ended secondary leaves a gap, nothing moves up.
     */
    #[Test]
    public function levelsComeBackInStoredOrderWithGapsKept(): void
    {
        $rows = [
            self::row(3, 'tertiary', 103, '2020-01-01', null),
            self::row(1, 'primary', 101, '2020-01-01', null),
            self::row(2, 'secondary', 102, '2020-01-01', '2026-01-01'),
        ];

        self::assertSame(['primary #1 company 101', 'tertiary #3 company 103'], self::chosen($rows));
    }

    /**
     * A type that isn't exactly primary, secondary or tertiary (null, blank, "Primary") belongs to no level.
     */
    #[Test]
    public function rowsWithNoLevelAreIgnored(): void
    {
        $rows = [
            self::row(1, null, 101, '2020-01-01', null),
            self::row(2, '', 102, '2020-01-01', null),
            self::row(3, 'Primary', 103, '2020-01-01', null),
        ];

        self::assertSame([], self::chosen($rows));
    }

    /**
     * A year with another insurer between two spells of the first: each day gets the policy in force on it.
     */
    #[Test]
    public function aChangeOfInsurerAndBackFollowsTheDay(): void
    {
        $rows = [
            self::row(1, 'primary', 101, '2020-01-01', null),
            self::row(2, 'primary', 102, '2025-01-01', '2025-12-31'),
            self::row(3, 'primary', 101, '2026-01-01', null),
        ];

        self::assertSame(['primary #1 company 101'], self::chosen($rows, '2024-12-31'));
        self::assertSame(['primary #2 company 102'], self::chosen($rows, '2025-01-01'));
        self::assertSame(['primary #2 company 102'], self::chosen($rows, '2025-12-31'));
        self::assertSame(['primary #3 company 101'], self::chosen($rows, '2026-01-01'));
    }

    /**
     * Ids and companies as a read may return them: ints, digit strings, leading zeros, surrounding spaces.
     */
    #[Test]
    public function idsAndCompaniesAreReadAsTheyArrive(): void
    {
        $rows = [
            self::row('1', 'primary', '101', '2020-01-01', null),
            self::row(2, 'secondary', 102, '2020-01-01', null),
            self::row(3, 'tertiary', '0103', '2020-01-01', null),
        ];

        self::assertSame(
            ['primary #1 company 101', 'secondary #2 company 102', 'tertiary #3 company 103'],
            self::chosen($rows)
        );
        self::assertSame(['primary #4 company 104'], self::chosen([self::row(4, 'primary', ' 104 ', null, null)]));
    }

    /**
     * The day asked about is the date's own calendar day: its time is dropped and its zone isn't converted.
     */
    #[Test]
    public function theDayIsTheCalendarDayGivenWhateverItsTimeOrZone(): void
    {
        $rows = [self::row(1, 'primary', 101, '2026-11-04', '2026-11-04')];

        $lateInTheDay = new DateTimeImmutable('2026-11-04 23:59:59', new DateTimeZone('UTC'));
        // Still the 3rd in UTC, but the 4th where the date was given.
        $earlyEastOfUtc = new DateTimeImmutable('2026-11-04 01:00:00', new DateTimeZone('Pacific/Kiritimati'));

        self::assertCount(1, EffectiveInsuranceRule::choose($rows, $lateInTheDay));
        self::assertCount(1, EffectiveInsuranceRule::choose($rows, $earlyEastOfUtc));
    }

    /**
     * A policy carries its row id, its uuid as text (null when the bytes aren't one), its company and dates;
     * a zero end reads as open.
     */
    #[Test]
    public function aPolicyCarriesItsRowIdUuidCompanyAndDates(): void
    {
        $uuid = '0e7a9d3c-6f1b-4c2a-9d8e-1f2a3b4c5d6e';
        $rows = [
            ['id' => 5, 'uuid' => UuidRegistry::uuidToBytes($uuid), 'type' => 'primary', 'provider' => '101', 'date' => '2026-01-01', 'date_end' => '2026-12-31'],
            ['id' => 6, 'uuid' => 'not sixteen bytes', 'type' => 'secondary', 'provider' => '102', 'date' => null, 'date_end' => '0000-00-00'],
        ];

        [$primary, $secondary] = EffectiveInsuranceRule::choose($rows, new DateTimeImmutable(self::DAY));

        self::assertSame(InsuranceLevel::Primary, $primary->level);
        self::assertSame(5, $primary->id);
        self::assertSame($uuid, $primary->uuid);
        self::assertSame(101, $primary->companyId);
        self::assertSame('2026-01-01', $primary->start?->format('Y-m-d'));
        self::assertSame('2026-12-31', $primary->end?->format('Y-m-d'));

        self::assertSame(InsuranceLevel::Secondary, $secondary->level);
        self::assertNull($secondary->uuid);
        self::assertNull($secondary->start);
        self::assertNull($secondary->end, 'a zero end is read as no end');
    }

    /**
     * @return array<string, array{string, ?string, string, ?string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function partialDates(): array
    {
        // sql_mode = '' can store a day or a month of 00, or a day past the month's end. choose() compares them as
        // stored; the period it reports is the days that comparison keeps.
        return [
            'an end with a zero day: in force through the day before the 1st' => ['2020-01-01', '2026-05-00', '2026-04-30', '2020-01-01', '2026-04-30'],
            'a start with a zero day: in force from the 1st' => ['2026-05-00', null, '2026-05-01', '2026-05-01', null],
            'an end with a zero month: in force through 31 December before' => ['2020-01-01', '2026-00-00', '2025-12-31', '2020-01-01', '2025-12-31'],
            'a start with a zero month: in force from 1 January' => ['2026-00-00', null, '2026-01-01', '2026-01-01', null],
            "an end past the month's end: in force through its last day" => ['2020-01-01', '2026-02-30', '2026-02-28', '2020-01-01', '2026-02-28'],
            "a start past the month's end: in force from the next month" => ['2026-02-30', null, '2026-03-01', '2026-03-01', null],
            'a zero start still means always' => ['0000-00-00', null, '2026-04-30', null, null],
        ];
    }

    /**
     * A partial date is compared as stored, and the start and end reported are the first and last days that
     * comparison keeps.
     */
    #[Test]
    #[DataProvider('partialDates')]
    public function aPartialDateIsReportedAsTheDaysTheRuleKeeps(string $start, ?string $end, string $day, ?string $first, ?string $last): void
    {
        $rows = [self::row(1, 'primary', 101, $start, $end)];

        $policies = EffectiveInsuranceRule::choose($rows, new DateTimeImmutable($day));

        self::assertCount(1, $policies, 'in force on ' . $day);
        self::assertSame($first, $policies[0]->start?->format('Y-m-d'), 'the first day in force');
        self::assertSame($last, $policies[0]->end?->format('Y-m-d'), 'the last day in force');
    }

    /**
     * No rows give an empty list, not an error.
     */
    #[Test]
    public function aPatientWithNoRowsHasNoCoverage(): void
    {
        self::assertSame([], EffectiveInsuranceRule::choose([], new DateTimeImmutable(self::DAY)));
    }
}
