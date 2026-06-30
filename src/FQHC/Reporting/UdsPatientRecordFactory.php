<?php

/**
 * Turns a resolved ReportingPatient into the per-table UDS record types.
 *
 * Pure: it runs the demographic classifiers and the age bands over one patient's
 * inputs, with no database or global state. A patient who cannot be placed in an
 * age- or sex-keyed table (Table 3A, and Table 4's age-split insurance) yields
 * null for that record — the generator counts those as data-quality drops rather
 * than inventing an age or sex. Table 3B and the ZIP table always produce a
 * record (their missing-data cases are real reportable buckets).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use OpenEMR\FQHC\Payer\UdsPayerClassifier;

final class UdsPatientRecordFactory
{
    private UdsRaceClassifier $raceClassifier;
    private UdsEthnicityClassifier $ethnicityClassifier;
    private UdsSexClassifier $sexClassifier;
    private LanguageBarrierRule $languageBarrierRule;
    private UdsPayerClassifier $payerClassifier;

    public function __construct(
        ?UdsRaceClassifier $raceClassifier = null,
        ?UdsEthnicityClassifier $ethnicityClassifier = null,
        ?UdsSexClassifier $sexClassifier = null,
        ?LanguageBarrierRule $languageBarrierRule = null,
        ?UdsPayerClassifier $payerClassifier = null,
    ) {
        $this->raceClassifier = $raceClassifier ?? new UdsRaceClassifier();
        $this->ethnicityClassifier = $ethnicityClassifier ?? new UdsEthnicityClassifier();
        $this->sexClassifier = $sexClassifier ?? new UdsSexClassifier();
        $this->languageBarrierRule = $languageBarrierRule ?? new LanguageBarrierRule();
        $this->payerClassifier = $payerClassifier ?? new UdsPayerClassifier();
    }

    public function table3a(ReportingPatient $patient): ?Table3aPatientRecord
    {
        $sex = $this->sexClassifier->classify($patient->sexCode);
        if ($patient->ageYears === null || $sex === null) {
            return null;
        }

        return new Table3aPatientRecord(Table3aAgeBand::fromAge($patient->ageYears), $sex);
    }

    public function table3b(ReportingPatient $patient): Table3bPatientRecord
    {
        return new Table3bPatientRecord(
            $this->raceClassifier->classify($patient->raceCode),
            $this->ethnicityClassifier->classify($patient->ethnicityCode),
            $this->languageBarrierRule->bestServedInNonEnglishLanguage(
                $patient->languageCode,
                $patient->interpreterNeeded,
            ),
        );
    }

    public function table4(ReportingPatient $patient): ?Table4PatientRecord
    {
        if ($patient->ageYears === null) {
            return null;
        }

        return new Table4PatientRecord(
            $patient->incomeBand,
            $this->payerClassifier->classifyByInsuranceTypeCode($patient->insuranceTypeCode),
            UdsAgeGroup::fromAge($patient->ageYears),
            $patient->specialPopulations,
        );
    }

    public function zip(ReportingPatient $patient): ZipCodeTablePatientRecord
    {
        return new ZipCodeTablePatientRecord(
            ZipResidence::fromRawZip($patient->zip),
            $this->payerClassifier->classifyByInsuranceTypeCode($patient->insuranceTypeCode),
        );
    }
}
