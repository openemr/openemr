<?php

/**
 * Isolated tests for the UDS Table 5 report generator and its presentation,
 * driven by an in-memory visit source.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Reporting\Table5ReportGenerator;
use OpenEMR\FQHC\Reporting\Table5VisitRecord;
use OpenEMR\FQHC\Reporting\Table5VisitSource;
use OpenEMR\FQHC\Reporting\UdsReportPresenter;
use OpenEMR\FQHC\Reporting\UdsServiceCategory;
use PHPUnit\Framework\TestCase;

final class Table5ReportGeneratorTest extends TestCase
{
    public function testGeneratesReportFromTheVisitSource(): void
    {
        $report = (new Table5ReportGenerator($this->sourceOf(
            new Table5VisitRecord(1, UdsServiceCategory::Medical, false),
            new Table5VisitRecord(1, UdsServiceCategory::Medical, true),
            new Table5VisitRecord(2, UdsServiceCategory::Medical, false),
            new Table5VisitRecord(3, UdsServiceCategory::Dental, false),
        )))->generateForYear(2025);

        self::assertSame(2, $report->clinicVisits(UdsServiceCategory::Medical));
        self::assertSame(1, $report->virtualVisits(UdsServiceCategory::Medical));
        self::assertSame(2, $report->patients(UdsServiceCategory::Medical));
        self::assertSame(1, $report->patients(UdsServiceCategory::Dental));
        self::assertSame(4, $report->grandTotalVisits());
        self::assertSame(3, $report->totalPatients());
    }

    public function testPresenterRendersTable5RowsAndTotals(): void
    {
        $report = (new Table5ReportGenerator($this->sourceOf(
            new Table5VisitRecord(1, UdsServiceCategory::Medical, false),
            new Table5VisitRecord(1, UdsServiceCategory::Medical, true),
            new Table5VisitRecord(3, UdsServiceCategory::Dental, false),
        )))->generateForYear(2025);

        $view = (new UdsReportPresenter())->table5($report);

        $medical = $this->row($view['rows'], 'Medical');
        self::assertSame(1, $medical['clinic']);
        self::assertSame(1, $medical['virtual']);
        self::assertSame(2, $medical['visits']);
        self::assertSame(1, $medical['patients']);
        self::assertSame(1, $this->row($view['rows'], 'Dental')['patients']);
        self::assertSame(2, $view['clinic']);
        self::assertSame(1, $view['virtual']);
        self::assertSame(3, $view['visits']);
        self::assertSame(2, $view['patients']);
    }

    private function sourceOf(Table5VisitRecord ...$visits): Table5VisitSource
    {
        return new class(array_values($visits)) implements Table5VisitSource {
            /**
             * @param list<Table5VisitRecord> $visits
             */
            public function __construct(private array $visits)
            {
            }

            public function visitsForYear(int $year): array
            {
                return $this->visits;
            }
        };
    }

    /**
     * @param iterable<mixed> $rows
     * @return array<array-key, mixed>
     */
    private function row(iterable $rows, string $label): array
    {
        foreach ($rows as $row) {
            if (is_array($row) && ($row['label'] ?? null) === $label) {
                return $row;
            }
        }

        self::fail('No row labelled "' . $label . '"');
    }
}
