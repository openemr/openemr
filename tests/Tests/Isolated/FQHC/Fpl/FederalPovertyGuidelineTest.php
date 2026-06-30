<?php

/**
 * Isolated tests for the Federal Poverty Level guideline value object.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Fpl;

use DomainException;
use OpenEMR\FQHC\Fpl\FederalPovertyGuideline;
use OpenEMR\FQHC\Fpl\FplRegion;
use PHPUnit\Framework\TestCase;

final class FederalPovertyGuidelineTest extends TestCase
{
    public function testThresholdForOnePersonIsBase(): void
    {
        $guideline = new FederalPovertyGuideline(2025, FplRegion::Contiguous, 10000.0, 4000.0);

        self::assertSame(10000.0, $guideline->annualThresholdFor(1));
    }

    public function testThresholdAddsPerPersonIncrement(): void
    {
        $guideline = new FederalPovertyGuideline(2025, FplRegion::Contiguous, 10000.0, 4000.0);

        self::assertSame(18000.0, $guideline->annualThresholdFor(3));
    }

    public function testThresholdTreatsSizeBelowOneAsOne(): void
    {
        $guideline = new FederalPovertyGuideline(2025, FplRegion::Contiguous, 10000.0, 4000.0);

        self::assertSame(10000.0, $guideline->annualThresholdFor(0));
    }

    public function testRejectsNonPositiveBase(): void
    {
        $this->expectException(DomainException::class);
        new FederalPovertyGuideline(2025, FplRegion::Contiguous, 0.0, 4000.0);
    }

    public function testRejectsNegativeIncrement(): void
    {
        $this->expectException(DomainException::class);
        new FederalPovertyGuideline(2025, FplRegion::Contiguous, 10000.0, -1.0);
    }
}
