<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar\ViewModel;

use OpenEMR\PostCalendar\ViewModel\SearchResultsBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('postcalendar')]
final class SearchResultsBuilderTest extends TestCase
{
    /** @var list<list<int|string>> ids passed to each provider lookup call */
    private array $lookups = [];

    /**
     * A builder whose provider lookup records its calls and answers from the given rows.
     *
     * @param list<array{id: int, fname: string, phonew1: string, street: string, city: string, state: string}> $users
     */
    private function builder(array $users): SearchResultsBuilder
    {
        return new SearchResultsBuilder(
            /** @param list<int|string> $ids */
            function (array $ids) use ($users): array {
                $this->lookups[] = $ids;
                $wanted = array_map(static fn(int|string $id): string => (string) $id, $ids);
                return array_values(array_filter(
                    $users,
                    static fn(array $user): bool => in_array((string) $user['id'], $wanted, true)
                ));
            },
            'contact info'
        );
    }

    /**
     * @return list<array{id: int, fname: string, phonew1: string, street: string, city: string, state: string}>
     */
    private static function users(): array
    {
        return [
            ['id' => 1, 'fname' => 'Alice', 'phonew1' => '555-0101', 'street' => '1 Main St', 'city' => 'Springfield', 'state' => 'IL'],
            ['id' => 2, 'fname' => 'Bob', 'phonew1' => '555-0202', 'street' => '2 Oak Ave', 'city' => 'Shelbyville', 'state' => 'IL'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function event(int|string|null $aid, string $eid, string $startTime = '09:30:00'): array
    {
        return [
            'aid' => $aid,
            'eid' => $eid,
            'startTime' => $startTime,
            'provider_name' => 'Provider ' . $eid,
            'catname' => 'Office Visit',
            'patient_name' => 'Patient ' . $eid,
        ];
    }

    /**
     * Rows carry the same keys and text the inline pnuser.php loop produced.
     */
    public function testRowsKeepTheLegacyShape(): void
    {
        $rows = $this->builder(self::users())->build([
            '2026-03-04' => [self::event('1', '10'), self::event(null, '11', '14:05:00')],
        ]);

        $this->assertSame([
            [
                'event_id_token' => '10~20260304',
                'datetime_display' => '2026-03-04 09:30 am',
                'provider_name' => 'Provider 10',
                'provider_info_title' => "Alice contact info:\n555-0101\n1 Main St\nSpringfield IL",
                'catname' => 'Office Visit',
                'patient_name' => 'Patient 10',
            ],
            [
                'event_id_token' => '11~20260304',
                'datetime_display' => '2026-03-04 02:05 pm',
                'provider_name' => 'Provider 11',
                'provider_info_title' => " contact info:\n",
                'catname' => 'Office Visit',
                'patient_name' => 'Patient 11',
            ],
        ], $rows);
    }

    /**
     * The providers of the whole result set are fetched in one call, each id once (#13492).
     */
    public function testProvidersAreLookedUpOnceWithDistinctIds(): void
    {
        $rows = $this->builder(self::users())->build([
            '2026-03-04' => [self::event('1', '10'), self::event(2, '11'), self::event('1', '12')],
            '2026-03-05' => [self::event('2', '13'), self::event('1', '14')],
        ]);

        $this->assertSame([['1', 2]], $this->lookups, 'One lookup for the whole result set, each id once');
        $this->assertSame(
            ['Alice', 'Bob', 'Alice', 'Bob', 'Alice'],
            array_map(static fn(array $row): string => strstr($row['provider_info_title'], ' ', true) ?: '', $rows)
        );
    }

    /**
     * Events without a provider id do not trigger a lookup.
     */
    public function testNoLookupWhenNoEventHasAProvider(): void
    {
        $rows = $this->builder(self::users())->build([
            '2026-03-04' => [self::event(null, '10'), self::event('', '11')],
        ]);

        $this->assertSame([], $this->lookups);
        $this->assertCount(2, $rows);
        $this->assertSame(" contact info:\n", $rows[0]['provider_info_title']);
    }

    /**
     * A provider id the lookup does not return gets the title without contact lines.
     */
    public function testUnknownProviderGetsTheBareTitle(): void
    {
        $rows = $this->builder(self::users())->build([
            '2026-03-04' => [self::event('99', '10')],
        ]);

        $this->assertSame([['99']], $this->lookups);
        $this->assertSame(" contact info:\n", $rows[0]['provider_info_title']);
    }

    /**
     * Non-string dates, non-list days and non-array events are skipped, and give no ids.
     */
    public function testMalformedEntriesAreSkipped(): void
    {
        $rows = $this->builder(self::users())->build([
            7 => [self::event('1', '1')],
            '2026-03-04' => 'not a list',
            '2026-03-05' => ['not an event', self::event('2', '20')],
        ]);

        $this->assertSame([['2']], $this->lookups, 'Ids come only from well-formed entries');
        $this->assertSame(['20~20260305'], array_column($rows, 'event_id_token'));
    }

    /**
     * Fields of the wrong type fall back to empty values, as in the inline loop.
     */
    public function testMalformedEventFieldsFallBackToEmptyValues(): void
    {
        $rows = $this->builder(self::users())->build([
            '2026-03-04' => [[
                'aid' => ['1'],
                'eid' => ['x'],
                'startTime' => 930,
                'provider_name' => null,
                'catname' => 5,
            ]],
        ]);

        $this->assertSame([], $this->lookups);
        $this->assertSame([
            'event_id_token' => '~20260304',
            'datetime_display' => '2026-03-04 12:00 am',
            'provider_name' => '',
            'provider_info_title' => " contact info:\n",
            'catname' => '',
            'patient_name' => '',
        ], $rows[0]);
    }

    /**
     * A date key strtotime() cannot parse gives an empty date/time display.
     */
    public function testUnparsableDateGivesEmptyDisplay(): void
    {
        $rows = $this->builder(self::users())->build(['not-a-date' => [self::event('1', '10')]]);

        $this->assertSame('', $rows[0]['datetime_display']);
    }

    /**
     * Lookup rows without a usable id are not matched to any event.
     */
    public function testLookupRowsWithoutAnIdAreIgnored(): void
    {
        $builder = new SearchResultsBuilder(
            static fn(array $ids): array => [['fname' => 'NoId'], ['id' => 1.5, 'fname' => 'FloatId']],
            'contact info'
        );

        $rows = $builder->build(['2026-03-04' => [self::event('1', '10')]]);

        $this->assertSame(" contact info:\n", $rows[0]['provider_info_title']);
    }
}
