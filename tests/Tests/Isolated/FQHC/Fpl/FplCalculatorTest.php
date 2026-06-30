<?php

/**
 * Isolated tests for the FPL calculator.
 *
 * Uses a synthetic guideline (base 10,000 / +4,000 per person) so the tests
 * verify the calculation and band boundaries rather than any year's real
 * dollar figures.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Fpl;

use OpenEMR\FQHC\Fpl\FederalPovertyGuideline;
use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Fpl\FplCalculator;
use OpenEMR\FQHC\Fpl\FplRegion;
use OpenEMR\FQHC\Fpl\IncomeDetermination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FplCalculatorTest extends TestCase
{
    private FplCalculator $calculator;
    private FederalPovertyGuideline $guideline;

    protected function setUp(): void
    {
        $this->calculator = new FplCalculator();
        $this->guideline = new FederalPovertyGuideline(2025, FplRegion::Contiguous, 10000.0, 4000.0);
    }

    #[DataProvider('bandProvider')]
    public function testBandAndPercent(int $householdSize, float $income, int $expectedPercent, FplBand $expectedBand): void
    {
        $result = $this->calculator->calculate(
            new IncomeDetermination($householdSize, $income),
            $this->guideline,
        );

        self::assertSame($expectedPercent, $result->percent);
        self::assertSame($expectedBand, $result->band);
    }

    /**
     * @return array<string, array{int, float, int, FplBand}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function bandProvider(): array
    {
        // household 1 => threshold 10,000; household 3 => threshold 18,000
        return [
            'at 100%' => [1, 10000.0, 100, FplBand::AtOrBelow100],
            'below 100%' => [1, 9000.0, 90, FplBand::AtOrBelow100],
            'at 150% boundary' => [1, 15000.0, 150, FplBand::From101To150],
            'just over 150% (rounds to 150 but bands up)' => [1, 15001.0, 150, FplBand::From151To200],
            'at 200% boundary' => [1, 20000.0, 200, FplBand::From151To200],
            'just over 200%' => [1, 20001.0, 200, FplBand::Above200],
            'well over 200%' => [1, 30000.0, 300, FplBand::Above200],
            'household of 3 at 100%' => [3, 18000.0, 100, FplBand::AtOrBelow100],
            'household of 3 at 50%' => [3, 9000.0, 50, FplBand::AtOrBelow100],
        ];
    }

    public function testUnknownWhenHouseholdSizeMissing(): void
    {
        $result = $this->calculator->calculate(new IncomeDetermination(null, 10000.0), $this->guideline);

        self::assertNull($result->percent);
        self::assertSame(FplBand::Unknown, $result->band);
    }

    public function testUnknownWhenIncomeMissing(): void
    {
        $result = $this->calculator->calculate(new IncomeDetermination(2, null), $this->guideline);

        self::assertSame(FplBand::Unknown, $result->band);
    }

    public function testUnknownWhenExplicitlyDeclined(): void
    {
        $result = $this->calculator->calculate(
            new IncomeDetermination(2, 10000.0, unknown: true),
            $this->guideline,
        );

        self::assertSame(FplBand::Unknown, $result->band);
    }
}
