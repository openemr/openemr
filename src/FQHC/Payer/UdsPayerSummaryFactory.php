<?php

/**
 * Builds a UdsPayerSummary from a patient's primary insurance.
 *
 * Pure presentation logic: no coverage maps to None/uninsured; coverage with a
 * recognised type maps to its UDS bucket; coverage with an unrecognised type is
 * marked unclassified (rather than guessed). Unit-testable in isolation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Payer;

final class UdsPayerSummaryFactory
{
    private UdsPayerClassifier $classifier;

    public function __construct(?UdsPayerClassifier $classifier = null)
    {
        $this->classifier = $classifier ?? new UdsPayerClassifier();
    }

    public function create(?PatientPrimaryInsurance $insurance): UdsPayerSummary
    {
        if ($insurance === null) {
            return new UdsPayerSummary(
                hasCoverage: false,
                classified: true,
                categoryLabel: UdsPayerCategory::None->label(),
                planName: null,
            );
        }

        $category = $this->classifier->classifyByInsuranceTypeCode($insurance->insuranceTypeCode);

        return new UdsPayerSummary(
            hasCoverage: true,
            classified: $category !== null,
            categoryLabel: $category !== null ? $category->label() : 'Unclassified',
            planName: $insurance->planName,
        );
    }
}
