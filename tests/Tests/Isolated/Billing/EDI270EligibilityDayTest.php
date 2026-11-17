<?php

/**
 * The day a 270 asks about (EDI270::eligibilityDay()): the day its DTP*291
 * sends.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Billing\EDI270;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EDI270EligibilityDayTest extends TestCase
{
    private const TODAY = '2026-11-04';

    /**
     * @return array<string, array{mixed, mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function days(): array
    {
        return [
            "a partner sending appointment dates: the appointment's" => ['2026-12-15', ['x12_dtp03' => 'A'], '2026-12-15'],
            'a partner sending the effective date: today' => ['2026-12-15', ['x12_dtp03' => 'E'], self::TODAY],
            "a partner sending today's date: today" => ['2026-12-15', ['x12_dtp03' => 'D'], self::TODAY],
            'no partner chosen (a list of partners): today' => ['2026-12-15', [['x12_dtp03' => 'A']], self::TODAY],
            'no appointment date: today' => [null, ['x12_dtp03' => 'A'], self::TODAY],
            'an appointment date that cannot be read: today' => ['12/15/2026', ['x12_dtp03' => 'A'], self::TODAY],
            'an appointment before 2001-01-02, which DTP*291 does not send: today' => ['2001-01-01', ['x12_dtp03' => 'A'], self::TODAY],
        ];
    }

    #[Test]
    #[DataProvider('days')]
    public function theDayIsTheOneDtp291Sends(mixed $appointmentDate, mixed $partner, string $expected): void
    {
        $clock = new FrozenClock(new DateTimeImmutable(self::TODAY . ' 15:00:00'));

        self::assertSame($expected, EDI270::eligibilityDay($appointmentDate, $partner, $clock)->format('Y-m-d'));
    }

    /**
     * The day agrees with the date createDTP() writes for an appointment row.
     */
    #[Test]
    public function theDayIsTheDateCreateDtpSends(): void
    {
        $clock = new FrozenClock(new DateTimeImmutable(self::TODAY . ' 15:00:00'));
        $partner = ['x12_dtp03' => 'A'];
        $segment = EDI270::createDTP(['pc_eventDate' => '20261215'], '291', $partner, '~', ':');

        self::assertSame('DTP*291*D8*20261215~', $segment);
        self::assertSame('20261215', EDI270::eligibilityDay('2026-12-15', $partner, $clock)->format('Ymd'));
    }
}
