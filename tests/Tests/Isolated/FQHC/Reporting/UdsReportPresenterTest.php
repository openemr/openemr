<?php

/**
 * Isolated tests for the UDS report presenter.
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
use OpenEMR\FQHC\Reporting\Clinical\Table6bReportBuilder;
use OpenEMR\FQHC\Reporting\Clinical\UdsClinicalMeasure;
use OpenEMR\FQHC\Reporting\Clinical\UdsMeasurePopulationCounts;
use OpenEMR\FQHC\Reporting\ReportingPatient;
use OpenEMR\FQHC\Reporting\ReportingPatientSource;
use OpenEMR\FQHC\Reporting\UdsPatientCharacteristicsReport;
use OpenEMR\FQHC\Reporting\UdsReportGenerator;
use OpenEMR\FQHC\Reporting\UdsReportPresenter;
use PHPUnit\Framework\TestCase;

final class UdsReportPresenterTest extends TestCase
{
    public function testPresentsEverySectionWithConsistentSummary(): void
    {
        $view = (new UdsReportPresenter())->present($this->report(
            // age 10, female, Hispanic white, Spanish speaker, Medicaid, ZIP 02118
            $this->patient(1, 10, 'Female', 'white', 'hisp_or_latin', 'spanish', FplBand::AtOrBelow100, '02118', 3),
            // age 40, male, non-Hispanic Chinese, English, Medicare, ZIP 10001
            $this->patient(2, 40, 'Male', 'chinese', 'not_hisp_or_latin', 'english', FplBand::Above200, '10001', 2),
        ));

        self::assertSame(2025, $view['summary']['year']);
        self::assertSame(2, $view['summary']['cohortSize']);
        self::assertTrue($view['summary']['consistent']);

        // Income (Table 4 Lines 1–6)
        self::assertSame(1, $this->row($view['income']['rows'], '100% and below')['count']);
        self::assertSame(1, $this->row($view['income']['rows'], 'Over 200%')['count']);
        self::assertSame(2, $view['income']['total']);

        // Insurance (Lines 7–12) with the age split
        $medicaid = $this->row($view['insurance']['rows'], 'Medicaid');
        self::assertSame(1, $medicaid['age0to17']);
        self::assertSame(0, $medicaid['age18AndOver']);
        $medicare = $this->row($view['insurance']['rows'], 'Medicare');
        self::assertSame(1, $medicare['age18AndOver']);
        self::assertSame(2, $view['insurance']['total']);

        // Age & sex (Table 3A)
        self::assertSame(1, $view['ageSex']['male']);
        self::assertSame(1, $view['ageSex']['female']);
        self::assertSame(2, $view['ageSex']['total']);

        // Race & ethnicity (Table 3B)
        self::assertSame(1, $this->row($view['race']['rows'], 'White')['hispanic']);
        self::assertSame(1, $this->row($view['race']['rows'], 'Chinese')['notHispanic']);
        self::assertTrue($this->row($view['race']['rows'], 'Chinese')['detail'], 'Asian sub-category is detail');
        self::assertSame(1, $view['race']['hispanic']);
        self::assertSame(1, $view['race']['language']);

        // ZIP table
        self::assertSame(1, $this->row($view['zip']['rows'], '02118')['publicInsurance']);
        self::assertSame(1, $this->row($view['zip']['rows'], '10001')['medicare']);
        self::assertSame(2, $view['zip']['total']);
    }

    public function testSpecialPopulationsAndHomelessUnknownRowArePresent(): void
    {
        $view = (new UdsReportPresenter())->present($this->report(
            $this->patient(1, 30, 'Male', 'white', 'not_hisp_or_latin', 'english', FplBand::Unknown, '02118', 8),
        ));

        $labels = array_column($view['specialPopulations'], 'label');
        self::assertContains('Total homeless', $labels);
        self::assertContains('Homeless — Unknown', $labels);
        self::assertContains('Veterans', $labels);
    }

    private function report(ReportingPatient ...$patients): UdsPatientCharacteristicsReport
    {
        $source = new class(array_values($patients)) implements ReportingPatientSource {
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

        return (new UdsReportGenerator($source))->generateForYear(2025);
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

    public function testPresentsTable6bAndTable7Rows(): void
    {
        $table6b = (new Table6bReportBuilder())->build(2025, [
            UdsClinicalMeasure::ControllingHighBloodPressure->cmsId() => new UdsMeasurePopulationCounts(
                initialPopulation: 100,
                denominator: 100,
                denominatorExclusions: 0,
                denominatorExceptions: 0,
                numerator: 70,
            ),
        ]);

        $presenter = new UdsReportPresenter();
        $table6bView = $presenter->table6b($table6b);
        $table7View = $presenter->table7($table6b);

        $computedRow = $this->row($table6bView['rows'], 'Controlling High Blood Pressure');
        self::assertTrue($computedRow['computed']);
        self::assertSame('CMS165v14', $computedRow['cmsId']);
        self::assertSame(100, $computedRow['denominator']);
        self::assertSame(70, $computedRow['numerator']);
        self::assertEqualsWithDelta(0.7, $computedRow['rate'], 0.0001);

        $pendingRow = $this->row($table6bView['rows'], 'Cervical Cancer Screening');
        self::assertFalse($pendingRow['computed']);
        self::assertNull($pendingRow['denominator']);
        self::assertNull($pendingRow['rate']);

        self::assertCount(2, $table7View['rows']);
        $table7Row = $this->row($table7View['rows'], 'Controlling High Blood Pressure');
        self::assertTrue($table7Row['computed']);
        self::assertEqualsWithDelta(0.7, $table7Row['rate'], 0.0001);
        self::assertFalse($this->row($table7View['rows'], 'Diabetes: Glycemic Status Assessment > 9%')['computed']);
    }

    private function patient(
        int $pid,
        int $ageYears,
        string $sexCode,
        string $raceCode,
        string $ethnicityCode,
        string $languageCode,
        FplBand $incomeBand,
        string $zip,
        ?int $insuranceTypeCode,
    ): ReportingPatient {
        return new ReportingPatient(
            pid: $pid,
            ageYears: $ageYears,
            sexCode: $sexCode,
            raceCode: $raceCode,
            ethnicityCode: $ethnicityCode,
            languageCode: $languageCode,
            interpreterNeeded: 'no',
            zip: $zip,
            incomeBand: $incomeBand,
            insuranceTypeCode: $insuranceTypeCode,
            specialPopulations: [],
        );
    }
}
