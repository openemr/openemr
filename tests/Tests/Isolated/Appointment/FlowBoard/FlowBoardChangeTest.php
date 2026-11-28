<?php

/**
 * Isolated FlowBoardChange Test
 *
 * Changing a status or room from the Flow Board: what is saved, when the arrival encounter is
 * created, and what is refused.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Appointment\FlowBoard;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Appointment\FlowBoard\FlowBoardChange;
use OpenEMR\Appointment\FlowBoard\FlowBoardChangeResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FlowBoardChangeTest extends TestCase
{
    private const TODAY = '2026-11-28';

    private static function change(FakeFlowBoardStore $store, bool $autoCreateEncounters = true): FlowBoardChange
    {
        // Noon, so the date is the same in any time zone the tests run in.
        $now = new DateTimeImmutable(self::TODAY . ' 12:00:00', new DateTimeZone(date_default_timezone_get()));
        return new FlowBoardChange($store, $autoCreateEncounters, new FrozenClock($now));
    }

    public function testAStatusChangeKeepsTheRoomAndCarriesTheTrackerEncounter(): void
    {
        $store = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn('2026-11-30'), trackerEncounter: 41);

        $result = self::change($store)->apply(768, '>', null, 'admin');

        $this->assertSame(FlowBoardChangeResult::Saved, $result);
        $this->assertSame([['eventId' => 768, 'user' => 'admin', 'status' => '>', 'room' => '1', 'encounter' => 41]], $store->saved);
    }

    public function testARoomChangeKeepsTheStatusEvenWhenItIsNoLongerActive(): void
    {
        $store = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn('2026-11-30'));

        $this->assertSame(FlowBoardChangeResult::Saved, self::change($store)->apply(768, null, '2', 'admin'));
        $this->assertSame(FlowBoardChangeResult::Saved, self::change($store)->apply(768, null, '', 'admin'));

        $this->assertSame(['*', '*'], array_column($store->saved, 'status'));
        $this->assertSame(['2', ''], array_column($store->saved, 'room'));
    }

    public function testArrivingTodayCreatesTheEncounter(): void
    {
        $store = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn(self::TODAY));

        self::change($store)->apply(768, '@', null, 'admin');

        $this->assertSame([768], $store->createdEncounters);
        $this->assertSame(900, $store->saved[0]['encounter']);
    }

    /**
     * @return array<string, array{string, string, bool, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function noEncounterProvider(): array
    {
        return [
            'another day' => ['2026-11-30', '@', true, 0],
            'setting off' => [self::TODAY, '@', false, 0],
            'tracker has one' => [self::TODAY, '@', true, 41],
            'not a check-in' => [self::TODAY, '>', true, 0],
        ];
    }

    #[DataProvider('noEncounterProvider')]
    public function testNoEncounterIsCreatedOtherwise(string $date, string $status, bool $autoCreate, int $trackerEncounter): void
    {
        $store = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn($date), trackerEncounter: $trackerEncounter);

        self::change($store, $autoCreate)->apply(768, $status, null, 'admin');

        $this->assertSame([], $store->createdEncounters);
        $this->assertSame($trackerEncounter, $store->saved[0]['encounter']);
    }

    /**
     * @return array<string, array{?string, ?string, FlowBoardChangeResult}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedProvider(): array
    {
        return [
            'unknown status' => ['zz', null, FlowBoardChangeResult::UnknownStatus],
            'unknown room' => [null, '9', FlowBoardChangeResult::UnknownRoom],
        ];
    }

    #[DataProvider('refusedProvider')]
    public function testValuesNotInTheActiveListsAreRefused(?string $status, ?string $room, FlowBoardChangeResult $expected): void
    {
        $store = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn('2026-11-30'));

        $this->assertSame($expected, self::change($store)->apply(768, $status, $room, 'admin'));
        $this->assertSame([], $store->saved);
    }

    public function testARepeatingOrMissingAppointmentIsNotChanged(): void
    {
        $repeating = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn(self::TODAY, repeating: true));
        $missing = new FakeFlowBoardStore(null);

        $this->assertSame(FlowBoardChangeResult::Repeating, self::change($repeating)->apply(768, '@', null, 'admin'));
        $this->assertSame(FlowBoardChangeResult::NotFound, self::change($missing)->apply(768, '@', null, 'admin'));
        $this->assertSame([], $repeating->saved);
        $this->assertSame([], $repeating->createdEncounters);
    }

    public function testASaveRefusedBecauseTheAppointmentNowRepeatsIsAConflict(): void
    {
        $store = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn('2026-11-30'), refuseSave: true);

        $this->assertSame(FlowBoardChangeResult::Repeating, self::change($store)->apply(768, null, '2', 'admin'));
        $this->assertSame([], $store->saved);
    }
}
