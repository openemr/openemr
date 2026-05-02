<?php

/**
 * Mock service for synthesizing eligibility (270/271) responses from the
 * patient's existing OpenEMR insurance + demographic rows.
 *
 * Mirrors PaymentAdviceMockService's approach: when the global Test Mode
 * toggle is on, EligibilityTransfer routes through this service instead
 * of the real ClaimRevApi so demos / training / development can exercise
 * the eligibility UI end-to-end without hitting a live payer.
 *
 * Contract: the returned array has the same shape ClaimRevApi::uploadEligibility()
 * produces, so EligibilityTransfer::saveEligibility() can persist it
 * without any conditional handling. Specifically:
 *
 *   - responseMessage              : success-style string
 *   - claimRevResultId             : deterministic per (pid, payerResponsibility)
 *                                    so re-running the same mock produces the
 *                                    same id (useful for AI-chat lookups)
 *   - mappedData.individuals[0].eligibility[0]
 *                                  : with status / payerInfo / mapped271 /
 *                                    subscriberId / policyDate / etc.
 *   - mappedData.individuals[0].coverageDiscovery[0]   (only if product 3 requested)
 *   - mappedData.individuals[0].demographicInfo        (only if product 2 requested)
 *   - mappedData.individuals[0].mbiFinderResults       (only if product 5 requested)
 *
 * The mock does NOT fabricate a `raw271` EDI string — that field stays
 * unset, which means `EligibilityTransfer::populateNativeEligibility()`
 * skips its native-271 import path. Demo mode populates the ClaimRev
 * eligibility card (the module's UI surface); the OpenEMR Insurance card's
 * native Eligibility tab stays empty until a real payer response arrives.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Database\QueryUtils;

class EligibilityMockService
{
    /**
     * Build a mock SharpRevenue eligibility response for the given patient.
     *
     * @param  list<int> $productsToRun Product IDs (1=eligibility, 2=demographics,
     *                                  3=coverage discovery, 5=MBI finder)
     * @return array<string, mixed>
     */
    public static function buildResponse(
        int $pid,
        string $payerResponsibility,
        array $productsToRun = [1],
    ): array {
        $patientData = EligibilityData::getPatientData($pid);
        $patient = is_array($patientData) ? $patientData : [];

        $insuranceRows = EligibilityData::getInsuranceData($pid, ValueMapping::mapPayerResponsibility($payerResponsibility));
        $insurancePolicyNumber = '';
        $insurancePayerName = 'Mock Payer';
        $insurancePayerId = '99999';
        $insuranceGroupNumber = '';

        if ($insuranceRows !== []) {
            // getInsuranceData returns a slim shape (payer_responsibility only).
            // Pull the full insurance record for the policy number / group / etc.
            $fullInsurance = self::lookupInsuranceCompany($pid, $payerResponsibility);
            $insurancePolicyNumber = $fullInsurance['policy_number'] ?? '';
            $insurancePayerName = $fullInsurance['payer_name'] ?? 'Mock Payer';
            $insurancePayerId = $fullInsurance['payer_number'] ?? '99999';
            $insuranceGroupNumber = $fullInsurance['group_number'] ?? '';
        }

        $pcn = $pid . '-' . substr($payerResponsibility, 0, 1);
        $mockId = 'mock-' . md5($pcn);

        $individual = [];

        if (in_array(1, $productsToRun, true)) {
            // Two eligibility entries: a current Active Coverage record
            // and a prior Inactive (terminated) record, so the demo
            // exercises the multi-entry template path.
            $individual['eligibility'] = [
                self::buildActiveEntry(
                    $patient,
                    $insurancePolicyNumber,
                    $insurancePayerName,
                    $insurancePayerId,
                    $insuranceGroupNumber,
                ),
                self::buildInactiveEntry($patient),
            ];
        }
        if (in_array(2, $productsToRun, true)) {
            $individual['demographicInfo'] = self::buildDemographics($patient);
        }
        if (in_array(3, $productsToRun, true)) {
            $individual['coverageDiscovery'] = [self::buildCoverageDiscovery($insurancePayerName, $insurancePayerId)];
            $individual['insuranceFinderStatus'] = 'complete';
        }
        if (in_array(5, $productsToRun, true)) {
            $individual['mbiFinderResults'] = self::buildMbiResult($patient);
        }

        return [
            'responseMessage' => 'Mock test mode result',
            'claimRevResultId' => $mockId,
            'isFatalError' => false,
            'mappedData' => [
                'status' => 'Complete',
                'individuals' => [$individual],
            ],
        ];
    }

    /**
     * Build an Active Coverage eligibility entry — the patient's current
     * primary insurance, with a current-year policy window and a full
     * benefits list.
     *
     * @param  array<string, mixed> $patient
     * @return array<string, mixed>
     */
    private static function buildActiveEntry(
        array $patient,
        string $policyNumber,
        string $payerName,
        string $payerId,
        string $groupNumber,
    ): array {
        $today = date('Y-m-d');
        $startOfYear = date('Y') . '-01-01';
        $endOfYear = date('Y') . '-12-31';

        $subscriberId = $policyNumber !== ''
            ? $policyNumber
            : 'MOCK-' . substr(md5(TypeCoerce::asString($patient['lname'] ?? '') . TypeCoerce::asString($patient['fname'] ?? '')), 0, 8);

        // Subscriber and dependent point at the same patient for the simple
        // self-coverage case the mock represents.
        $subscriberInfo = [
            'firstName' => TypeCoerce::asString($patient['fname'] ?? 'Demo'),
            'lastName' => TypeCoerce::asString($patient['lname'] ?? 'Patient'),
            'dateOfBirth' => TypeCoerce::asString($patient['dob'] ?? '1980-01-01'),
            'gender' => TypeCoerce::asString($patient['sex'] ?? 'U'),
            'memberId' => $subscriberId,
            'address' => [
                'address1' => TypeCoerce::asString($patient['street'] ?? ''),
                'city' => TypeCoerce::asString($patient['city'] ?? ''),
                'state' => TypeCoerce::asString($patient['state'] ?? ''),
                'zip' => TypeCoerce::asString($patient['postal_code'] ?? ''),
            ],
            'benefits' => self::buildBenefits(),
        ];

        return [
            'status' => 'Active Coverage',
            'subscriberId' => $subscriberId,
            'insuranceType' => 'PPO',
            'planSponsor' => $payerName,
            'planCode' => 'MOCK-PLAN',
            'insurancePlan' => 'Mock Active PPO',
            'groupNumber' => $groupNumber !== '' ? $groupNumber : 'MOCK-GRP',
            'groupName' => $payerName . ' Group',
            'policyDate' => [
                'startDate' => $startOfYear,
                'endDate' => $endOfYear,
                'addedDate' => $today,
            ],
            'payerInfo' => [
                'payerName' => $payerName,
                'payerCode' => $payerId,
            ],
            // Top-level deductible summary the Deductibles tab renders.
            'deductible' => 1500.00,
            'deductibleRemaining' => 750.00,
            'outOfPocket' => 5000.00,
            'outOfPocketRemaining' => 3200.00,
            'deductibles' => self::buildDeductibles(),
            'mapped271' => [
                'informationSourceName' => $payerName,
                'receiver' => [
                    'name' => 'Demo Provider Group',
                    'identifier' => '1234567890',
                ],
                'subscriber' => $subscriberInfo,
            ],
            // raw271 deliberately omitted — see class docblock.
        ];
    }

    /**
     * Build an Inactive (terminated) eligibility entry — a prior carrier
     * the patient was on, with a policy that ended a few months ago.
     *
     * Returned alongside the Active entry so the demo exercises the
     * multi-eligibility template path (one tab per entry, one shows
     * "Active Coverage" in green, one shows the terminated status in red).
     *
     * @param  array<string, mixed> $patient
     * @return array<string, mixed>
     */
    private static function buildInactiveEntry(array $patient): array
    {
        // Terminated 90 days ago, ran for 1 year before that.
        $terminationDate = date('Y-m-d', strtotime('-90 days'));
        $priorStartDate = date('Y-m-d', strtotime('-1 year -90 days'));

        $priorMemberId = 'OLD-' . substr(
            md5(TypeCoerce::asString($patient['lname'] ?? '') . '-prior'),
            0,
            8
        );

        return [
            'status' => 'Inactive',
            'subscriberId' => $priorMemberId,
            'insuranceType' => 'HMO',
            'planSponsor' => 'Mock Previous Employer',
            'planCode' => 'MOCK-PRIOR',
            'insurancePlan' => 'Mock Terminated HMO',
            'groupNumber' => 'MOCK-OLDGRP',
            'groupName' => 'Mock Previous Employer Group',
            'policyDate' => [
                'startDate' => $priorStartDate,
                'endDate' => $terminationDate,
                'addedDate' => $priorStartDate,
            ],
            'payerInfo' => [
                'payerName' => 'Prior Mock Insurance Co',
                'payerCode' => '88888',
            ],
            'mapped271' => [
                'informationSourceName' => 'Prior Mock Insurance Co',
                'receiver' => [
                    'name' => 'Demo Provider Group',
                    'identifier' => '1234567890',
                ],
                'subscriber' => [
                    'firstName' => TypeCoerce::asString($patient['fname'] ?? 'Demo'),
                    'lastName' => TypeCoerce::asString($patient['lname'] ?? 'Patient'),
                    'dateOfBirth' => TypeCoerce::asString($patient['dob'] ?? '1980-01-01'),
                    'gender' => TypeCoerce::asString($patient['sex'] ?? 'U'),
                    'memberId' => $priorMemberId,
                    'address' => [
                        'address1' => TypeCoerce::asString($patient['street'] ?? ''),
                        'city' => TypeCoerce::asString($patient['city'] ?? ''),
                        'state' => TypeCoerce::asString($patient['state'] ?? ''),
                        'zip' => TypeCoerce::asString($patient['postal_code'] ?? ''),
                    ],
                    // Inactive coverage typically reports the cancellation
                    // status code (6) rather than a full benefits breakdown.
                    'benefits' => [
                        self::benefit(
                            code: '6',
                            desc: 'Inactive',
                            serviceTypes: [['serviceTypeCode' => '30', 'serviceTypeDesc' => 'Health Benefit Plan Coverage']],
                            planDesc: 'Coverage terminated ' . $terminationDate,
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * Build a benefits list using the X12 271 EB-segment shape the eligibility
     * templates render. Each entry uses `benefitInformation` (EB01 code) plus
     * the surrounding fields (serviceTypes[], coverageLevel, planCoverageDescription,
     * timePeriodQualifierDesc, benefitAmount, benefitPercent, etc.) so the
     * benefit_code_filter setting works against real EB01 codes (1, 6, A, B,
     * C, F, G, J, etc.).
     *
     * @return list<array<string, mixed>>
     */
    private static function buildBenefits(): array
    {
        return [
            self::benefit(
                code: '1',
                desc: 'Active Coverage',
                serviceTypes: [['serviceTypeCode' => '30', 'serviceTypeDesc' => 'Health Benefit Plan Coverage']],
                planDesc: 'Mock Active PPO Plan',
                insuranceTypeCodeDesc: 'Preferred Provider Organization',
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: 'C',
                desc: 'Deductible',
                serviceTypes: [['serviceTypeCode' => '30', 'serviceTypeDesc' => 'Health Benefit Plan Coverage']],
                timePeriodDesc: 'Calendar Year',
                amount: 1500.00,
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: 'C',
                desc: 'Deductible Remaining',
                serviceTypes: [['serviceTypeCode' => '30', 'serviceTypeDesc' => 'Health Benefit Plan Coverage']],
                timePeriodDesc: 'Remaining',
                amount: 750.00,
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: 'B',
                desc: 'Co-Payment',
                serviceTypes: [['serviceTypeCode' => 'BV', 'serviceTypeDesc' => 'Office Visit']],
                amount: 25.00,
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: 'B',
                desc: 'Co-Payment',
                serviceTypes: [['serviceTypeCode' => 'UC', 'serviceTypeDesc' => 'Urgent Care']],
                amount: 75.00,
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: 'A',
                desc: 'Co-Insurance',
                serviceTypes: [['serviceTypeCode' => '30', 'serviceTypeDesc' => 'Health Benefit Plan Coverage']],
                percent: 20.0,
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: 'G',
                desc: 'Out of Pocket (Stop Loss)',
                serviceTypes: [['serviceTypeCode' => '30', 'serviceTypeDesc' => 'Health Benefit Plan Coverage']],
                timePeriodDesc: 'Calendar Year',
                amount: 5000.00,
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: '1',
                desc: 'Active Coverage',
                serviceTypes: [['serviceTypeCode' => '88', 'serviceTypeDesc' => 'Pharmacy']],
                planDesc: 'Mock Pharmacy Benefit',
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: '1',
                desc: 'Active Coverage',
                serviceTypes: [['serviceTypeCode' => 'AL', 'serviceTypeDesc' => 'Vision (Optometry)']],
                planDesc: 'Mock Vision Benefit',
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: 'F',
                desc: 'Limitations',
                serviceTypes: [['serviceTypeCode' => 'MH', 'serviceTypeDesc' => 'Mental Health']],
                planDesc: '20 visits per calendar year',
                quantity: 20.0,
                quantityQualifierDesc: 'Visits',
                inPlanNetwork: 'Y',
            ),
            self::benefit(
                code: 'J',
                desc: 'Cost Containment',
                serviceTypes: [['serviceTypeCode' => '30', 'serviceTypeDesc' => 'Health Benefit Plan Coverage']],
                planDesc: 'Prior authorization required for certain services',
                authIndicator: 'Y',
            ),
        ];
    }

    /**
     * Helper: build a single EB-segment-shaped benefit. Keeps the
     * benefit list above readable and consistent.
     *
     * @param  list<array{serviceTypeCode: string, serviceTypeDesc: string}> $serviceTypes
     * @return array<string, mixed>
     */
    private static function benefit(
        string $code,
        string $desc,
        array $serviceTypes,
        string $coverageLevel = 'Individual',
        string $planDesc = '',
        string $insuranceTypeCodeDesc = '',
        string $timePeriodDesc = '',
        ?float $amount = null,
        ?float $percent = null,
        ?float $quantity = null,
        string $quantityQualifierDesc = '',
        string $authIndicator = '',
        string $inPlanNetwork = '',
    ): array {
        $row = [
            'benefitInformation' => $code,
            'benefitInformationDesc' => $desc,
            'coverageLevel' => $coverageLevel,
            'serviceTypes' => $serviceTypes,
            'planCoverageDescription' => $planDesc,
            'insuranceTypeCodeDesc' => $insuranceTypeCodeDesc,
            'timePeriodQualifierDesc' => $timePeriodDesc,
            'inPlanNetworkIndicator' => $inPlanNetwork,
        ];
        if ($amount !== null) {
            $row['benefitAmount'] = $amount;
        }
        if ($percent !== null) {
            $row['benefitPercent'] = $percent;
        }
        if ($quantity !== null) {
            $row['benefitQuantity'] = $quantity;
            $row['quantityQualifierDesc'] = $quantityQualifierDesc;
        }
        if ($authIndicator !== '') {
            $row['certificationIndicator'] = $authIndicator;
        }
        return $row;
    }

    /**
     * Build the deductibles[] table the Deductibles tab renders.
     * One row per service type so the demo shows multiple line items.
     *
     * @return list<array<string, mixed>>
     */
    private static function buildDeductibles(): array
    {
        return [
            [
                'serviceTypeCode' => '30',
                'serviceTypeDescription' => 'Health Benefit Plan Coverage',
                'coverageLevelCode' => 'IND',
                'coverageLevelDescription' => 'Individual',
                'insuranceTypeCode' => 'PPO',
                'insuranceTypeDescription' => 'Preferred Provider Organization',
                'inPlanNetwork' => 'Y',
                'annualAmount' => '1500.00',
                'episodeAmount' => '',
                'remainingAmount' => '750.00',
                'planName' => 'Mock Active PPO',
            ],
            [
                'serviceTypeCode' => '30',
                'serviceTypeDescription' => 'Health Benefit Plan Coverage',
                'coverageLevelCode' => 'FAM',
                'coverageLevelDescription' => 'Family',
                'insuranceTypeCode' => 'PPO',
                'insuranceTypeDescription' => 'Preferred Provider Organization',
                'inPlanNetwork' => 'Y',
                'annualAmount' => '3000.00',
                'episodeAmount' => '',
                'remainingAmount' => '1500.00',
                'planName' => 'Mock Active PPO',
            ],
            [
                'serviceTypeCode' => 'AL',
                'serviceTypeDescription' => 'Vision (Optometry)',
                'coverageLevelCode' => 'IND',
                'coverageLevelDescription' => 'Individual',
                'insuranceTypeCode' => 'PPO',
                'insuranceTypeDescription' => 'Preferred Provider Organization',
                'inPlanNetwork' => 'Y',
                'annualAmount' => '50.00',
                'episodeAmount' => '',
                'remainingAmount' => '50.00',
                'planName' => 'Mock Vision Add-on',
            ],
        ];
    }

    /**
     * Build a Demographics product result.
     *
     * @param  array<string, mixed> $patient
     * @return array<string, mixed>
     */
    private static function buildDemographics(array $patient): array
    {
        return [
            'status' => 'Complete',
            'confidenceScore' => 95,
            'confidenceScoreReason' => 'High-confidence mock match',
            'correctedPerson' => (object) [
                'firstName' => TypeCoerce::asString($patient['fname'] ?? 'Demo'),
                'lastName' => TypeCoerce::asString($patient['lname'] ?? 'Patient'),
                'dateOfBirth' => TypeCoerce::asString($patient['dob'] ?? '1980-01-01'),
                'gender' => TypeCoerce::asString($patient['sex'] ?? 'U'),
                'address1' => TypeCoerce::asString($patient['street'] ?? ''),
                'city' => TypeCoerce::asString($patient['city'] ?? ''),
                'state' => TypeCoerce::asString($patient['state'] ?? ''),
                'zip' => TypeCoerce::asString($patient['postal_code'] ?? ''),
            ],
        ];
    }

    /**
     * Build a Coverage Discovery product result.
     *
     * @return array<string, mixed>
     */
    private static function buildCoverageDiscovery(string $payerName, string $payerId): array
    {
        // Coverage Discovery returns the same shape as eligibility[] with a
        // discovered coverage row. The mock surfaces a single hit.
        return [
            'status' => 'Active Coverage',
            'subscriberId' => 'DISCOVERY-' . substr(md5($payerId), 0, 8),
            'insuranceType' => 'HMO',
            'payerInfo' => [
                'payerName' => $payerName,
                'payerCode' => $payerId,
            ],
        ];
    }

    /**
     * Build an MBI Finder product result.
     *
     * @param  array<string, mixed> $patient
     * @return array<string, mixed>
     */
    private static function buildMbiResult(array $patient): array
    {
        return [
            'status' => 'Found',
            'mbi' => '1AA-AA1-AA11', // CMS-format MBI: 11 chars, c-na-an-na pattern
            'confidence' => 'High',
            'firstName' => TypeCoerce::asString($patient['fname'] ?? 'Demo'),
            'lastName' => TypeCoerce::asString($patient['lname'] ?? 'Patient'),
            'dateOfBirth' => TypeCoerce::asString($patient['dob'] ?? '1980-01-01'),
        ];
    }

    /**
     * Look up the patient's primary insurance row for plan/payer details.
     * Returns an empty array when no insurance is on file (the caller falls
     * back to mock defaults).
     *
     * @return array<string, string>
     */
    private static function lookupInsuranceCompany(int $pid, string $payerResponsibility): array
    {
        $pr = ValueMapping::mapPayerResponsibility($payerResponsibility);
        $row = QueryUtils::querySingleRow(
            "SELECT i.policy_number, i.group_number,
                    c.name AS payer_name,
                    COALESCE(c.eligibility_id, c.cms_id) AS payer_number
               FROM insurance_data i
               JOIN insurance_companies c ON c.id = i.provider
              WHERE i.pid = ? AND i.type = ?
              ORDER BY i.date DESC
              LIMIT 1",
            [$pid, $pr]
        );

        if (!is_array($row)) {
            return [];
        }

        return [
            'policy_number' => TypeCoerce::asString($row['policy_number'] ?? ''),
            'group_number' => TypeCoerce::asString($row['group_number'] ?? ''),
            'payer_name' => TypeCoerce::asString($row['payer_name'] ?? 'Mock Payer'),
            'payer_number' => TypeCoerce::asString($row['payer_number'] ?? '99999'),
        ];
    }
}
