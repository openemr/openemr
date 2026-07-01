<?php

/**
 * Isolated tests for the UDS Table 5 utilization aggregator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Reporting\Table5ReportBuilder;
use OpenEMR\FQHC\Reporting\Table5VisitRecord;
use OpenEMR\FQHC\Reporting\UdsServiceCategory;
use PHPUnit\Framework\TestCase;

final class Table5ReportBuilderTest extends TestCase
{
    public function testEmptyInputProducesZeroReport(): void
    {
        $report = (new Table5ReportBuilder())->build([]);

        self::assertSame(0, $report->grandTotalVisits());
        self::assertSame(0, $report->totalPatients());
        self::assertSame(0, $report->patients(UdsServiceCategory::Medical));
    }

    public function testSeparatesInPersonAndVirtualVisits(): void
    {
        $report = (new Table5ReportBuilder())->build([
            new Table5VisitRecord(1, UdsServiceCategory::Medical, false),
            new Table5VisitRecord(1, UdsServiceCategory::Medical, false),
            new Table5VisitRecord(2, UdsServiceCategory::Medical, true),
        ]);

        self::assertSame(2, $report->clinicVisits(UdsServiceCategory::Medical));
        self::assertSame(1, $report->virtualVisits(UdsServiceCategory::Medical));
        self::assertSame(3, $report->totalVisits(UdsServiceCategory::Medical));
        self::assertSame(2, $report->totalClinicVisits());
        self::assertSame(1, $report->totalVirtualVisits());
        self::assertSame(3, $report->grandTotalVisits());
    }

    public function testPatientsAreUnduplicatedWithinACategory(): void
    {
        // Same patient, three medical visits → three visits, one patient.
        $report = (new Table5ReportBuilder())->build([
            new Table5VisitRecord(7, UdsServiceCategory::Medical, false),
            new Table5VisitRecord(7, UdsServiceCategory::Medical, false),
            new Table5VisitRecord(7, UdsServiceCategory::Medical, true),
        ]);

        self::assertSame(3, $report->totalVisits(UdsServiceCategory::Medical));
        self::assertSame(1, $report->patients(UdsServiceCategory::Medical));
    }

    public function testPatientSeenInTwoCategoriesCountsInEach(): void
    {
        // One patient, seen for medical and dental → counted once in each
        // category (duplicated across); grand-total patients is therefore 2.
        $report = (new Table5ReportBuilder())->build([
            new Table5VisitRecord(9, UdsServiceCategory::Medical, false),
            new Table5VisitRecord(9, UdsServiceCategory::Dental, false),
        ]);

        self::assertSame(1, $report->patients(UdsServiceCategory::Medical));
        self::assertSame(1, $report->patients(UdsServiceCategory::Dental));
        self::assertSame(2, $report->totalPatients());
        self::assertSame(2, $report->grandTotalVisits());
    }

    public function testCountsAreScopedPerCategory(): void
    {
        $report = (new Table5ReportBuilder())->build([
            new Table5VisitRecord(1, UdsServiceCategory::Vision, false),
            new Table5VisitRecord(2, UdsServiceCategory::MentalHealth, true),
        ]);

        self::assertSame(1, $report->clinicVisits(UdsServiceCategory::Vision));
        self::assertSame(0, $report->virtualVisits(UdsServiceCategory::Vision));
        self::assertSame(1, $report->virtualVisits(UdsServiceCategory::MentalHealth));
        self::assertSame(0, $report->clinicVisits(UdsServiceCategory::MentalHealth));
        self::assertSame(0, $report->totalVisits(UdsServiceCategory::Medical));
    }
}
