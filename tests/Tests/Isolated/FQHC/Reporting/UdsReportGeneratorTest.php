<?php

/**
 * Isolated tests for the UDS report generator, driven by an in-memory source.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Reporting\ReportingPatient;
use OpenEMR\FQHC\Reporting\ReportingPatientSource;
use OpenEMR\FQHC\Reporting\UdsReportGenerator;
use PHPUnit\Framework\TestCase;

final class UdsReportGeneratorTest extends TestCase
{
    public function testGeneratesConsistentReportForACompleteCohort(): void
    {
        $source = $this->sourceOf(
            $this->patient(1, ageYears: 10, sexCode: 'Female', insuranceTypeCode: 3),
            $this->patient(2, ageYears: 40, sexCode: 'Male', insuranceTypeCode: 3),
            $this->patient(3, ageYears: 70, sexCode: 'Female', insuranceTypeCode: 3),
        );

        $report = (new UdsReportGenerator($source))->generateForYear(2025);

        self::assertSame(2025, $report->year);
        self::assertSame(3, $report->cohortSize);
        self::assertSame(3, $report->table3a->total());
        self::assertSame(3, $report->table3b->totalPatients());
        self::assertSame(3, $report->zipCodeTable->total());
        self::assertSame(3, $report->table4->totalPatients);
        self::assertTrue($report->reconciliation->isConsistent());
    }

    public function testPatientMissingAgeIsDroppedFromAgeTablesAndSurfacedByReconciliation(): void
    {
        $source = $this->sourceOf(
            $this->patient(1, ageYears: 25, sexCode: 'Male', insuranceTypeCode: 3),
            $this->patient(2, ageYears: null, sexCode: 'Male', insuranceTypeCode: 3),
        );

        $report = (new UdsReportGenerator($source))->generateForYear(2025);

        self::assertSame(2, $report->cohortSize);
        self::assertSame(1, $report->table3a->total(), 'no-age patient dropped from Table 3A');
        self::assertSame(1, $report->table4->totalPatients, 'no-age patient dropped from Table 4');
        self::assertSame(2, $report->table3b->totalPatients(), 'Table 3B still counts everyone');
        self::assertSame(2, $report->zipCodeTable->total(), 'ZIP table still counts everyone');
        self::assertFalse(
            $report->reconciliation->patientTotalsAgree(),
            'the data-quality drop makes the totals disagree',
        );
    }

    public function testEmptyCohortProducesEmptyConsistentReport(): void
    {
        $report = (new UdsReportGenerator($this->sourceOf()))->generateForYear(2025);

        self::assertSame(0, $report->cohortSize);
        self::assertSame(0, $report->table3b->totalPatients());
        self::assertTrue($report->reconciliation->isConsistent());
    }

    private function sourceOf(ReportingPatient ...$patients): ReportingPatientSource
    {
        return new class(array_values($patients)) implements ReportingPatientSource {
            /**
             * @param list<ReportingPatient> $patients
             */
            public function __construct(private array $patients)
            {
            }

            public function cohortForYear(int $year): array
            {
                return array_map(static fn(ReportingPatient $patient): int => $patient->pid, $this->patients);
            }

            public function load(int $pid, int $year): ReportingPatient
            {
                foreach ($this->patients as $patient) {
                    if ($patient->pid === $pid) {
                        return $patient;
                    }
                }

                throw new \RuntimeException('Unknown pid ' . $pid);
            }
        };
    }

    private function patient(
        int $pid,
        ?int $ageYears,
        ?string $sexCode,
        ?int $insuranceTypeCode,
    ): ReportingPatient {
        return new ReportingPatient(
            pid: $pid,
            ageYears: $ageYears,
            sexCode: $sexCode,
            raceCode: 'white',
            ethnicityCode: 'not_hisp_or_latin',
            languageCode: 'english',
            interpreterNeeded: 'no',
            zip: '02118',
            incomeBand: FplBand::AtOrBelow100,
            insuranceTypeCode: $insuranceTypeCode,
            specialPopulations: [],
        );
    }
}
