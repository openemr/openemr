<?php

/**
 * Database-backed source of the UDS reporting cohort and per-patient inputs.
 *
 * The cohort is every patient with a visit in the reporting calendar year
 * (`form_encounter`). For each patient it reads the raw `patient_data` codes,
 * computes the age as of December 31 of the year, and reuses the existing FQHC
 * repositories for insurance, income/FPL, and special-population status — so this
 * class is only the thin SQL boundary; all classification stays in the pure
 * factory it feeds.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use DateTimeImmutable;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Fpl\FplCalculator;
use OpenEMR\FQHC\Fpl\FplGuidelineRepository;
use OpenEMR\FQHC\Fpl\FplRegion;
use OpenEMR\FQHC\Income\PatientIncomeRepository;
use OpenEMR\FQHC\Payer\PatientPayerRepository;
use OpenEMR\FQHC\Snapshot\AgeCalculator;
use OpenEMR\FQHC\SpecialPopulation\PatientSpecialPopulationRepository;

final class ReportingPatientRepository implements ReportingPatientSource
{
    private PatientPayerRepository $payerRepository;
    private PatientIncomeRepository $incomeRepository;
    private FplGuidelineRepository $guidelineRepository;
    private FplCalculator $fplCalculator;
    private PatientSpecialPopulationRepository $specialPopulationRepository;

    public function __construct(
        ?PatientPayerRepository $payerRepository = null,
        ?PatientIncomeRepository $incomeRepository = null,
        ?FplGuidelineRepository $guidelineRepository = null,
        ?FplCalculator $fplCalculator = null,
        ?PatientSpecialPopulationRepository $specialPopulationRepository = null,
    ) {
        $this->payerRepository = $payerRepository ?? new PatientPayerRepository();
        $this->incomeRepository = $incomeRepository ?? new PatientIncomeRepository();
        $this->guidelineRepository = $guidelineRepository ?? new FplGuidelineRepository();
        $this->fplCalculator = $fplCalculator ?? new FplCalculator();
        $this->specialPopulationRepository = $specialPopulationRepository ?? new PatientSpecialPopulationRepository();
    }

    public function cohortForYear(int $year): array
    {
        $start = sprintf('%04d-01-01 00:00:00', $year);
        $end = sprintf('%04d-01-01 00:00:00', $year + 1);

        $rows = QueryUtils::fetchTableColumn(
            'SELECT DISTINCT pid FROM form_encounter WHERE date >= ? AND date < ? ORDER BY pid',
            'pid',
            [$start, $end],
        );

        $pids = [];
        foreach ($rows as $value) {
            if (is_numeric($value)) {
                $pids[] = (int) $value;
            }
        }

        return $pids;
    }

    public function load(int $pid, int $year): ReportingPatient
    {
        $row = QueryUtils::querySingleRow(
            'SELECT DOB, sex, race, ethnicity, language, interpreter_needed, postal_code '
            . 'FROM patient_data WHERE pid = ? LIMIT 1',
            [$pid],
        );
        $row = is_array($row) ? $row : [];

        $asOf = new DateTimeImmutable(sprintf('%04d-12-31', $year));

        return new ReportingPatient(
            pid: $pid,
            ageYears: AgeCalculator::years($this->stringField($row, 'DOB'), $asOf),
            sexCode: $this->stringField($row, 'sex'),
            raceCode: $this->stringField($row, 'race'),
            ethnicityCode: $this->stringField($row, 'ethnicity'),
            languageCode: $this->stringField($row, 'language'),
            interpreterNeeded: $this->stringField($row, 'interpreter_needed'),
            zip: $this->stringField($row, 'postal_code'),
            incomeBand: $this->incomeBandFor($pid),
            insuranceTypeCode: $this->payerRepository->findPrimaryByPid($pid)?->insuranceTypeCode,
            specialPopulations: $this->specialPopulationRepository->findByPid($pid),
        );
    }

    private function incomeBandFor(int $pid): FplBand
    {
        $income = $this->incomeRepository->findByPid($pid);
        if ($income === null) {
            return FplBand::Unknown;
        }

        $guideline = $this->guidelineRepository->findLatestForRegion(FplRegion::Contiguous);
        if ($guideline === null) {
            return FplBand::Unknown;
        }

        return $this->fplCalculator->calculate($income, $guideline)->band;
    }

    /**
     * @param array<mixed> $row
     */
    private function stringField(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
