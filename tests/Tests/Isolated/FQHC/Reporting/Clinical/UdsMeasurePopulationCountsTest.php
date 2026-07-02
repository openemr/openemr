<?php

/**
 * Isolated tests for UDS clinical measure population count arithmetic.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting\Clinical;

use OpenEMR\FQHC\Reporting\Clinical\UdsMeasurePopulationCounts;
use PHPUnit\Framework\TestCase;

final class UdsMeasurePopulationCountsTest extends TestCase
{
    public function testEligibleDenominatorRemovesExclusionsAndExceptions(): void
    {
        $counts = new UdsMeasurePopulationCounts(
            initialPopulation: 100,
            denominator: 100,
            denominatorExclusions: 10,
            denominatorExceptions: 5,
            numerator: 60,
        );

        self::assertSame(85, $counts->eligibleDenominator());
        self::assertEqualsWithDelta(60 / 85, $counts->rate(), 0.0001);
    }

    public function testRateIsNullWhenNoPatientIsEligible(): void
    {
        $counts = new UdsMeasurePopulationCounts(
            initialPopulation: 10,
            denominator: 10,
            denominatorExclusions: 10,
            denominatorExceptions: 0,
            numerator: 0,
        );

        self::assertSame(0, $counts->eligibleDenominator());
        self::assertNull($counts->rate());
    }
}
