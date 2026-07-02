<?php

/**
 * Isolated tests for the UDS Table 6B report generator, driven by an
 * in-memory measure result source.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting\Clinical;

use OpenEMR\FQHC\Reporting\Clinical\CqmMeasureResultSource;
use OpenEMR\FQHC\Reporting\Clinical\PendingCqmMeasureResultSource;
use OpenEMR\FQHC\Reporting\Clinical\Table6bReportGenerator;
use OpenEMR\FQHC\Reporting\Clinical\UdsClinicalMeasure;
use OpenEMR\FQHC\Reporting\Clinical\UdsMeasurePopulationCounts;
use PHPUnit\Framework\TestCase;

final class Table6bReportGeneratorTest extends TestCase
{
    public function testReportsComputedResultsForMappedMeasures(): void
    {
        $counts = new UdsMeasurePopulationCounts(
            initialPopulation: 200,
            denominator: 200,
            denominatorExclusions: 0,
            denominatorExceptions: 0,
            numerator: 150,
        );

        $report = (new Table6bReportGenerator($this->sourceOf([
            UdsClinicalMeasure::ChildhoodImmunizationStatus->cmsId() => $counts,
        ])))->generateForYear(2025);

        self::assertSame(2025, $report->year);
        self::assertTrue($report->isComputed(UdsClinicalMeasure::ChildhoodImmunizationStatus));
        self::assertSame($counts, $report->resultFor(UdsClinicalMeasure::ChildhoodImmunizationStatus));
        self::assertFalse($report->isComputed(UdsClinicalMeasure::CervicalCancerScreening));
        self::assertNull($report->resultFor(UdsClinicalMeasure::CervicalCancerScreening));
    }

    public function testIgnoresResultsForCmsIdsOutsideTheMeasureMap(): void
    {
        $report = (new Table6bReportGenerator($this->sourceOf([
            'CMS999v1' => new UdsMeasurePopulationCounts(1, 1, 0, 0, 1),
        ])))->generateForYear(2025);

        foreach (UdsClinicalMeasure::cases() as $measure) {
            self::assertFalse($report->isComputed($measure));
        }
    }

    public function testPendingSourceReportsNothingComputed(): void
    {
        $report = (new Table6bReportGenerator(new PendingCqmMeasureResultSource()))->generateForYear(2025);

        foreach (UdsClinicalMeasure::cases() as $measure) {
            self::assertFalse($report->isComputed($measure));
        }
    }

    /**
     * @param array<string, UdsMeasurePopulationCounts> $results
     */
    private function sourceOf(array $results): CqmMeasureResultSource
    {
        return new class ($results) implements CqmMeasureResultSource {
            /**
             * @param array<string, UdsMeasurePopulationCounts> $results
             */
            public function __construct(private array $results)
            {
            }

            public function resultsForYear(int $year): array
            {
                return $this->results;
            }
        };
    }
}
