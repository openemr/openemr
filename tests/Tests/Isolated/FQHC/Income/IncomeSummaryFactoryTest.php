<?php

/**
 * Isolated tests for the income summary factory.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Income;

use OpenEMR\FQHC\Fpl\FederalPovertyGuideline;
use OpenEMR\FQHC\Fpl\FplRegion;
use OpenEMR\FQHC\Fpl\IncomeDetermination;
use OpenEMR\FQHC\Income\IncomeSummaryFactory;
use PHPUnit\Framework\TestCase;

final class IncomeSummaryFactoryTest extends TestCase
{
    private IncomeSummaryFactory $factory;
    private FederalPovertyGuideline $guideline;

    protected function setUp(): void
    {
        $this->factory = new IncomeSummaryFactory();
        // synthetic guideline: threshold 10,000 for a single person
        $this->guideline = new FederalPovertyGuideline(2025, FplRegion::Contiguous, 10000.0, 4000.0);
    }

    public function testRecordedSummaryCarriesComputedBandTierAndFormatting(): void
    {
        $summary = $this->factory->create(new IncomeDetermination(1, 12000.0), $this->guideline);

        self::assertTrue($summary->recorded);
        self::assertSame(1, $summary->householdSize);
        self::assertSame('$12,000', $summary->annualIncomeDisplay);
        self::assertSame(120, $summary->fplPercent);
        self::assertSame('101–150%', $summary->bandLabel);
        self::assertSame('Sliding fee — discount', $summary->tierLabel);
    }

    public function testUnknownIncomeProducesUnrecordedUnknownSummary(): void
    {
        $summary = $this->factory->create(new IncomeDetermination(null, null), $this->guideline);

        self::assertFalse($summary->recorded);
        self::assertNull($summary->fplPercent);
        self::assertSame('Unknown', $summary->bandLabel);
        self::assertSame('Undetermined', $summary->tierLabel);
        self::assertNull($summary->annualIncomeDisplay);
    }
}
