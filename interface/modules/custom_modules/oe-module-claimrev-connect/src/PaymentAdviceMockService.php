<?php

/**
 * Mock service for generating fake payment advice data from real OpenEMR encounters.
 *
 * Used for testing the Payment Advice UI and posting flow when no real
 * ERA data is available from ClaimRev (e.g. no test payer). Generates
 * realistic ClaimPaymentAggregation responses using actual billing data.
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

/**
 * @phpstan-import-type PaymentAdviceShape from PaymentAdvicePage
 */
class PaymentAdviceMockService
{
    /**
     * Generate mock payment advice results from real OpenEMR encounters.
     *
     * Queries recent billed encounters with billing line items and builds
     * fake ClaimPaymentAggregation responses that mirror the ClaimRev API format.
     *
     * @param array{patientFirstName?: string, patientLastName?: string, patientControlNumber?: string, receivedDateStart?: string, receivedDateEnd?: string, pageIndex?: int} $filters
     * @return array{results: list<PaymentAdviceShape>, totalRecords: int}
     */
    public static function generateMockResults(array $filters): array
    {
        $pageIndex = (int) ($filters['pageIndex'] ?? 0);
        $pageSize = 50;
        $offset = $pageIndex * $pageSize;

        // Build WHERE clause from filters
        $where = ["b.billed = 1", "b.activity = 1"];
        $params = [];

        $patientFirstName = $filters['patientFirstName'] ?? '';
        if ($patientFirstName !== '') {
            $where[] = "p.fname LIKE ?";
            $params[] = '%' . $patientFirstName . '%';
        }
        $patientLastName = $filters['patientLastName'] ?? '';
        if ($patientLastName !== '') {
            $where[] = "p.lname LIKE ?";
            $params[] = '%' . $patientLastName . '%';
        }
        $pcn = $filters['patientControlNumber'] ?? '';
        if ($pcn !== '') {
            $parts = preg_split('/[\s\-]/', $pcn);
            if (is_array($parts) && count($parts) >= 2) {
                $where[] = "e.pid = ?";
                $where[] = "e.encounter = ?";
                $params[] = (int) $parts[0];
                $params[] = (int) $parts[1];
            }
        }
        $receivedDateStart = $filters['receivedDateStart'] ?? '';
        if ($receivedDateStart !== '') {
            $where[] = "e.date >= ?";
            $params[] = $receivedDateStart . ' 00:00:00';
        }
        $receivedDateEnd = $filters['receivedDateEnd'] ?? '';
        if ($receivedDateEnd !== '') {
            $where[] = "e.date <= ?";
            $params[] = $receivedDateEnd . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $where);

        // Count total matching encounters
        $countSql = "SELECT COUNT(DISTINCT e.pid, e.encounter) AS cnt " .
            "FROM form_encounter e " .
            "JOIN patient_data p ON p.pid = e.pid " .
            "JOIN billing b ON b.pid = e.pid AND b.encounter = e.encounter " .
            "WHERE {$whereClause}";
        $totalRecords = TypeCoerce::asInt(QueryUtils::fetchSingleValue($countSql, 'cnt', $params));

        // Get distinct encounters with patient info
        $sql = "SELECT DISTINCT e.pid, e.encounter, e.date, e.facility_id, " .
            "p.fname, p.lname, p.mname, p.DOB, p.ss " .
            "FROM form_encounter e " .
            "JOIN patient_data p ON p.pid = e.pid " .
            "JOIN billing b ON b.pid = e.pid AND b.encounter = e.encounter " .
            "WHERE {$whereClause} " .
            "ORDER BY e.date DESC " .
            "LIMIT {$pageSize} OFFSET {$offset}";
        $encounters = QueryUtils::fetchRecords($sql, $params);

        $results = [];
        foreach ($encounters as $enc) {
            /** @var array<string, mixed> $enc */
            $result = self::buildMockPaymentAdvice($enc);
            if ($result !== null) {
                $results[] = PaymentAdvicePage::normalizeAdvice($result);
            }
        }

        return [
            'results' => $results,
            'totalRecords' => $totalRecords,
        ];
    }

    /**
     * Build a single mock ClaimPaymentAggregation from an encounter.
     *
     * @param  array<string, mixed> $enc Encounter row with patient data
     * @return array<string, mixed>|null Returned as PaymentAdviceShape via
     *     normalizeAdvice() in PaymentAdvicePage::searchPaymentInfo, but
     *     this raw mock response carries extra fields the real ClaimRev
     *     API returns too (servicePaymentInfos, accountNumber, etc).
     */
    private static function buildMockPaymentAdvice(array $enc): ?array
    {
        $pid = TypeCoerce::asInt($enc['pid'] ?? 0);
        $encounter = TypeCoerce::asInt($enc['encounter'] ?? 0);
        $pcn = $pid . '-' . $encounter;

        // Get billing line items for this encounter
        $billingRows = QueryUtils::fetchRecords(
            "SELECT code_type, code, modifier, fee, units " .
            "FROM billing WHERE pid = ? AND encounter = ? AND activity = 1 AND fee > 0 " .
            "ORDER BY id",
            [$pid, $encounter]
        );

        if ($billingRows === []) {
            return null;
        }

        // Get insurance info
        $insRow = QueryUtils::querySingleRow(
            "SELECT ic.name AS payer_name, ic.cms_id AS payer_number " .
            "FROM insurance_data id " .
            "JOIN insurance_companies ic ON ic.id = id.provider " .
            "WHERE id.pid = ? AND id.type = 'primary' " .
            "ORDER BY id.date DESC LIMIT 1",
            [$pid]
        );

        $payerName = is_array($insRow) ? TypeCoerce::asString($insRow['payer_name'] ?? 'Mock Payer') : 'Mock Payer';
        $payerNumber = is_array($insRow) ? TypeCoerce::asString($insRow['payer_number'] ?? '99999') : '99999';

        // Generate a deterministic fake ID from pid+encounter
        $fakeId = md5('mock-' . $pcn);

        $totalCharged = 0.0;
        $serviceLines = [];

        foreach ($billingRows as $row) {
            $fee = TypeCoerce::asFloat($row['fee'] ?? 0);
            $code = TypeCoerce::asString($row['code'] ?? '');
            $modifier = TypeCoerce::asString($row['modifier'] ?? '');
            $units = TypeCoerce::asFloat($row['units'] ?? 1);
            if ($units === 0.0) {
                $units = 1.0;
            }
            $totalCharged += $fee;

            // Simulate realistic payment: 70-90% paid, rest adjusted
            $payPercent = (crc32($pcn . $code) % 21 + 70) / 100; // 70-90%
            $paid = round($fee * $payPercent, 2);
            $coAdj = round(($fee - $paid) * 0.6, 2); // 60% of remainder is contractual
            $prAdj = round($fee - $paid - $coAdj, 2); // rest is patient responsibility

            $serviceLines[] = [
                'procedureCode' => $code,
                'procedureQualifier' => 'HC',
                'modifier1' => $modifier,
                'modifier2' => '',
                'modifier3' => '',
                'modifier4' => '',
                'chargeAmount' => $fee,
                'paymentAmount' => $paid,
                'revenueCode' => '',
                'unitsOfServicePaidCount' => $units,
                'originalUnitsOfServiceCount' => $units,
                'serviceDateStart' => substr(TypeCoerce::asString($enc['date'] ?? ''), 0, 10),
                'serviceDateEnd' => substr(TypeCoerce::asString($enc['date'] ?? ''), 0, 10),
                'adjudicatedProcedureCode' => $code,
                'procedureCodeDesc' => '',
                'contractedAmount' => null,
                'varianceFromContract' => null,
                'isUnderpaid' => null,
                'adjustmentGroups' => [
                    [
                        'groupCode' => 'CO',
                        'adjustments' => [
                            [
                                'reasonCode' => '45',
                                'reasonCodeDesc' => 'Charge exceeds fee schedule/maximum allowable',
                                'adjustmentAmount' => $coAdj,
                                'adjustmentQuantity' => 0,
                            ],
                        ],
                    ],
                    [
                        'groupCode' => 'PR',
                        'adjustments' => [
                            [
                                'reasonCode' => '1',
                                'reasonCodeDesc' => 'Deductible Amount',
                                'adjustmentAmount' => $prAdj,
                                'adjustmentQuantity' => 0,
                            ],
                        ],
                    ],
                ],
                'remarkCodes' => [],
                'paymentAmounts' => [],
            ];
        }

        $totalPaid = array_sum(array_column($serviceLines, 'paymentAmount'));
        $totalPatientResp = 0.0;
        foreach ($serviceLines as $svc) {
            foreach ($svc['adjustmentGroups'] as $group) {
                if ($group['groupCode'] === 'PR') {
                    foreach ($group['adjustments'] as $adj) {
                        $totalPatientResp += TypeCoerce::asFloat($adj['adjustmentAmount']);
                    }
                }
            }
        }

        // Randomly assign an eraClassification based on payment ratio
        $payRatio = $totalCharged > 0 ? $totalPaid / $totalCharged : 0;
        if ($payRatio > 0.5) {
            $eraClassification = 'Paid';
        } elseif ($payRatio > 0) {
            $eraClassification = 'PartiallyPaid';
        } else {
            $eraClassification = 'Denied';
        }

        $checkNumber = 'MOCK' . substr($fakeId, 0, 8);
        $encounterDate = substr(TypeCoerce::asString($enc['date'] ?? ''), 0, 10);
        // Simulate received date as a few days after encounter
        $receivedTs = strtotime($encounterDate . ' +7 days');
        $receivedDate = date('Y-m-d', $receivedTs !== false ? $receivedTs : time());

        return [
            'paymentAdviceId' => $fakeId,
            'accountNumber' => 'MOCK',
            'receivedDate' => $receivedDate . 'T00:00:00Z',
            'payerName' => $payerName,
            'payerNumber' => $payerNumber,
            'paymentAdviceStatusId' => 2, // Complete
            'eraClassification' => $eraClassification,
            'paymentInfo' => [
                'patientControlNumber' => $pcn,
                'claimStatusCode' => $eraClassification === 'Denied' ? '4' : '1',
                'totalClaimAmount' => $totalCharged,
                'claimPaymentAmount' => $totalPaid,
                'patientResponsibility' => $totalPatientResp,
                'payerControlNumber' => 'MOCK-' . $checkNumber,
                'patientFirstName' => TypeCoerce::asString($enc['fname'] ?? ''),
                'patientLastName' => TypeCoerce::asString($enc['lname'] ?? ''),
                'patientMiddleName' => TypeCoerce::asString($enc['mname'] ?? ''),
                'patientSuffix' => '',
                'patientIdentifier' => TypeCoerce::asString($enc['ss'] ?? ''),
                'insuredFirstName' => TypeCoerce::asString($enc['fname'] ?? ''),
                'insuredLastName' => TypeCoerce::asString($enc['lname'] ?? ''),
                'insuredMiddleName' => '',
                'insuredSuffix' => '',
                'insuredIdentifier' => '',
                'receivedDate' => $receivedDate . 'T00:00:00Z',
                'matchStatus' => 'Matched',
                'isWorked' => false,
                'servicePaymentInfos' => $serviceLines,
                'adjustmentGroups' => [],
                'claimTags' => [],
                'paymentAmounts' => [],
            ],
            'checkInformation' => [
                'checkNumber' => $checkNumber,
                'checkDate' => $receivedDate . 'T00:00:00Z',
                'totalActualProviderPaymentAmt' => $totalPaid,
                'transactionHandlingCode' => 'I',
                'transactionHandlingCodeDesc' => 'Remittance Information Only',
                'creditDebitFlag' => 'C',
                'paymentMethodCode' => 'CHK',
                'paymentFormatCode' => '',
                'payerIdentifier' => $payerNumber,
                'senderRoutingNumber' => '',
                'senderAccountNumber' => '',
                'depositRoutingNumber' => '',
                'depositAccountNumber' => '',
            ],
        ];
    }
}
