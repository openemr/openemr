<?php

/**
 * Detects UDS data-quality gaps on a reporting-year patient cohort.
 *
 * Pure and deterministic: it reuses the same classifiers the report
 * generator uses (UdsSexClassifier, UdsPayerClassifier) so a gap here means
 * exactly what it means in the report, and never re-derives classification
 * logic. A patient with no detected gap is omitted from the worklist.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\DataQuality;

use OpenEMR\FQHC\Fpl\FplBand;
use OpenEMR\FQHC\Payer\UdsPayerClassifier;
use OpenEMR\FQHC\Reporting\ReportingPatient;
use OpenEMR\FQHC\Reporting\UdsSexClassifier;

final class DataQualityWorklistBuilder
{
    private UdsSexClassifier $sexClassifier;
    private UdsPayerClassifier $payerClassifier;

    public function __construct(
        ?UdsSexClassifier $sexClassifier = null,
        ?UdsPayerClassifier $payerClassifier = null,
    ) {
        $this->sexClassifier = $sexClassifier ?? new UdsSexClassifier();
        $this->payerClassifier = $payerClassifier ?? new UdsPayerClassifier();
    }

    /**
     * @param iterable<ReportingPatient> $patients
     * @return list<PatientDataQualityIssues>
     */
    public function build(iterable $patients): array
    {
        $worklist = [];
        foreach ($patients as $patient) {
            $gaps = $this->gapsFor($patient);
            if ($gaps !== []) {
                $worklist[] = new PatientDataQualityIssues($patient->pid, $gaps);
            }
        }

        return $worklist;
    }

    /**
     * @return list<UdsDataQualityGap>
     */
    private function gapsFor(ReportingPatient $patient): array
    {
        $gaps = [];

        if ($patient->ageYears === null) {
            $gaps[] = UdsDataQualityGap::MissingAge;
        }

        if ($this->sexClassifier->classify($patient->sexCode) === null) {
            $gaps[] = UdsDataQualityGap::MissingSex;
        }

        if ($patient->incomeBand === FplBand::Unknown) {
            $gaps[] = UdsDataQualityGap::UnknownFplBand;
        }

        if (
            $patient->insuranceTypeCode !== null
            && $this->payerClassifier->classifyByInsuranceTypeCode($patient->insuranceTypeCode) === null
        ) {
            $gaps[] = UdsDataQualityGap::UnclassifiedInsurance;
        }

        return $gaps;
    }
}
