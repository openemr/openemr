<?php

/**
 * Isolated tests for the data-quality worklist generator and its
 * presentation, driven by an in-memory patient source.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting\DataQuality;

use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Reporting\DataQuality\DataQualityWorklistGenerator;
use OpenEMR\FQHC\Reporting\DataQuality\DataQualityWorklistPresenter;
use OpenEMR\FQHC\Reporting\DataQuality\UdsDataQualityGap;
use OpenEMR\FQHC\Reporting\ReportingPatient;
use OpenEMR\FQHC\Reporting\ReportingPatientSource;
use PHPUnit\Framework\TestCase;

final class DataQualityWorklistGeneratorTest extends TestCase
{
    public function testGeneratesTheWorklistFromTheReportingCohort(): void
    {
        $worklist = (new DataQualityWorklistGenerator($this->sourceOf(
            $this->patient(1, ageYears: 40, incomeBand: FplBand::From101To150),
            $this->patient(2, ageYears: null, incomeBand: FplBand::Unknown),
        )))->generateForYear(2025);

        self::assertSame(2025, $worklist->year);
        self::assertSame(1, $worklist->total());
        self::assertSame(1, $worklist->countOf(UdsDataQualityGap::MissingAge));
        self::assertSame(1, $worklist->countOf(UdsDataQualityGap::UnknownFplBand));
        self::assertSame(0, $worklist->countOf(UdsDataQualityGap::MissingSex));
    }

    public function testPresenterRendersGapCountsAndPatientRows(): void
    {
        $worklist = (new DataQualityWorklistGenerator($this->sourceOf(
            $this->patient(7, ageYears: null),
        )))->generateForYear(2025);

        $view = (new DataQualityWorklistPresenter())->present($worklist);

        self::assertSame(2025, $view['year']);
        self::assertSame(1, $view['total']);
        self::assertSame(7, $view['rows'][0]['pid']);
        self::assertSame(['Missing date of birth'], $view['rows'][0]['gaps']);

        $missingAgeCount = null;
        foreach ($view['gapCounts'] as $row) {
            if ($row['label'] === 'Missing date of birth') {
                $missingAgeCount = $row['count'];
            }
        }
        self::assertSame(1, $missingAgeCount);
    }

    private function sourceOf(ReportingPatient ...$patients): ReportingPatientSource
    {
        return new class (array_values($patients)) implements ReportingPatientSource {
            /**
             * @param list<ReportingPatient> $patients
             */
            public function __construct(private array $patients)
            {
            }

            public function cohortForYear(int $year): array
            {
                return array_map(static fn (ReportingPatient $patient): int => $patient->pid, $this->patients);
            }

            public function load(int $pid, int $year): ReportingPatient
            {
                foreach ($this->patients as $patient) {
                    if ($patient->pid === $pid) {
                        return $patient;
                    }
                }

                throw new \RuntimeException('No fixture patient with pid ' . $pid);
            }
        };
    }

    private function patient(
        int $pid,
        ?int $ageYears = 40,
        ?string $sexCode = 'Female',
        FplBand $incomeBand = FplBand::From101To150,
        ?int $insuranceTypeCode = 3,
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
            incomeBand: $incomeBand,
            insuranceTypeCode: $insuranceTypeCode,
            specialPopulations: [],
        );
    }
}
