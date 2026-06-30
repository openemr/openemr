<?php

/**
 * Isolated tests for the default sliding-fee schedule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Fpl;

use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Fpl\SlidingFeeSchedule;
use OpenEMR\FQHC\Fpl\SlidingFeeTier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SlidingFeeScheduleTest extends TestCase
{
    #[DataProvider('tierProvider')]
    public function testTierForBand(FplBand $band, SlidingFeeTier $expected): void
    {
        self::assertSame($expected, (new SlidingFeeSchedule())->tierFor($band));
    }

    /**
     * @return array<string, array{FplBand, SlidingFeeTier}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function tierProvider(): array
    {
        return [
            'at or below 100' => [FplBand::AtOrBelow100, SlidingFeeTier::NominalFee],
            '101 to 150' => [FplBand::From101To150, SlidingFeeTier::Discount],
            '151 to 200' => [FplBand::From151To200, SlidingFeeTier::PartialDiscount],
            'above 200' => [FplBand::Above200, SlidingFeeTier::FullCharge],
            'unknown' => [FplBand::Unknown, SlidingFeeTier::Undetermined],
        ];
    }
}
