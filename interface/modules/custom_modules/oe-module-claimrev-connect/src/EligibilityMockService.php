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
            $individual['eligibility'] = [self::buildEligibilityEntry(
                $patient,
                $insurancePolicyNumber,
                $insurancePayerName,
                $insurancePayerId,
                $insuranceGroupNumber,
            )];
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
     * Build the eligibility[] entry that drives the ClaimRev eligibility card.
     *
     * @param  array<string, mixed> $patient
     * @return array<string, mixed>
     */
    private static function buildEligibilityEntry(
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
     * Build a benefits list covering the common Service Type Codes the
     * eligibility templates render: 30 (health benefit), 88 (pharmacy),
     * AL (vision), MH (mental health), UC (urgent care).
     *
     * @return list<array<string, mixed>>
     */
    private static function buildBenefits(): array
    {
        return [
            [
                'code' => '1',
                'codeDesc' => 'Active Coverage',
                'coverageLevel' => 'IND',
                'coverageLevelDesc' => 'Individual',
                'serviceType' => '30',
                'serviceTypeDesc' => 'Health Benefit Plan Coverage',
                'insuranceType' => 'PPO',
                'insuranceTypeDesc' => 'Preferred Provider Organization',
            ],
            [
                'code' => 'C',
                'codeDesc' => 'Deductible',
                'coverageLevel' => 'IND',
                'coverageLevelDesc' => 'Individual',
                'serviceType' => '30',
                'serviceTypeDesc' => 'Health Benefit Plan Coverage',
                'timePeriod' => '23',
                'timePeriodDesc' => 'Calendar Year',
                'monetaryAmount' => 1500.00,
            ],
            [
                'code' => 'C',
                'codeDesc' => 'Deductible',
                'coverageLevel' => 'IND',
                'coverageLevelDesc' => 'Individual',
                'serviceType' => '30',
                'serviceTypeDesc' => 'Health Benefit Plan Coverage',
                'timePeriod' => '29',
                'timePeriodDesc' => 'Remaining',
                'monetaryAmount' => 750.00,
            ],
            [
                'code' => 'B',
                'codeDesc' => 'Co-Payment',
                'coverageLevel' => 'IND',
                'coverageLevelDesc' => 'Individual',
                'serviceType' => 'BV',
                'serviceTypeDesc' => 'Office Visit',
                'monetaryAmount' => 25.00,
            ],
            [
                'code' => 'A',
                'codeDesc' => 'Co-Insurance',
                'coverageLevel' => 'IND',
                'coverageLevelDesc' => 'Individual',
                'serviceType' => '30',
                'serviceTypeDesc' => 'Health Benefit Plan Coverage',
                'percent' => 0.20,
            ],
            [
                'code' => 'G',
                'codeDesc' => 'Out of Pocket (Stop Loss)',
                'coverageLevel' => 'IND',
                'coverageLevelDesc' => 'Individual',
                'serviceType' => '30',
                'serviceTypeDesc' => 'Health Benefit Plan Coverage',
                'timePeriod' => '23',
                'timePeriodDesc' => 'Calendar Year',
                'monetaryAmount' => 5000.00,
            ],
            [
                'code' => '1',
                'codeDesc' => 'Active Coverage',
                'coverageLevel' => 'IND',
                'coverageLevelDesc' => 'Individual',
                'serviceType' => '88',
                'serviceTypeDesc' => 'Pharmacy',
            ],
            [
                'code' => '1',
                'codeDesc' => 'Active Coverage',
                'coverageLevel' => 'IND',
                'coverageLevelDesc' => 'Individual',
                'serviceType' => 'AL',
                'serviceTypeDesc' => 'Vision (Optometry)',
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
        $row = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
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
