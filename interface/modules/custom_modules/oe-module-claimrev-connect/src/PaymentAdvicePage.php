<?php

/**
 * Payment Advice page controller.
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
 * @phpstan-type PaymentInfoShape array{
 *     patientFirstName: string,
 *     patientLastName: string,
 *     patientControlNumber: string,
 *     claimStatusCode: string,
 *     totalClaimAmount: float,
 *     claimPaymentAmount: float,
 *     patientResponsibility: float,
 *     isWorked: bool,
 *     ...
 * }
 * @phpstan-type CheckInfoShape array{
 *     checkNumber: string,
 *     checkDate: string,
 *     paymentMethodCode: string,
 *     paymentAmount: float,
 *     ...
 * }
 * @phpstan-type PaymentAdviceShape array{
 *     paymentAdviceId: string,
 *     receivedDate: string,
 *     payerName: string,
 *     payerNumber: string,
 *     eraClassification: string,
 *     paymentInfo: PaymentInfoShape,
 *     checkInformation: CheckInfoShape,
 *     ...
 * }
 */
class PaymentAdvicePage
{
    /**
     * Build a search model from POST data and execute the search.
     *
     * @param array{receivedDateStart?: string, receivedDateEnd?: string, serviceDateStart?: string, serviceDateEnd?: string, patientFirstName?: string, patientLastName?: string, payerNumber?: string, patientControlNumber?: string, checkNumber?: string, isWorked?: string, sortField?: string, sortDirection?: string, pageIndex?: int} $postData
     * @return array{results: list<PaymentAdviceShape>, totalRecords: int}
     */
    public static function searchPaymentInfo(array $postData): array
    {
        $pageIndex = $postData['pageIndex'] ?? 0;

        $model = new PaymentAdviceSearchModel();
        $model->receivedDateStart = ($postData['receivedDateStart'] ?? '') !== '' ? $postData['receivedDateStart'] : null;
        $model->receivedDateEnd = ($postData['receivedDateEnd'] ?? '') !== '' ? $postData['receivedDateEnd'] : null;
        $model->serviceDateStart = ($postData['serviceDateStart'] ?? '') !== '' ? $postData['serviceDateStart'] : null;
        $model->serviceDateEnd = ($postData['serviceDateEnd'] ?? '') !== '' ? $postData['serviceDateEnd'] : null;
        $model->patientFirstName = ($postData['patientFirstName'] ?? '') !== '' ? $postData['patientFirstName'] : null;
        $model->patientLastName = ($postData['patientLastName'] ?? '') !== '' ? $postData['patientLastName'] : null;
        $model->payerNumber = ($postData['payerNumber'] ?? '') !== '' ? $postData['payerNumber'] : null;
        $model->patientControlNumber = ($postData['patientControlNumber'] ?? '') !== '' ? $postData['patientControlNumber'] : null;
        $model->checkNumber = ($postData['checkNumber'] ?? '') !== '' ? $postData['checkNumber'] : null;

        $isWorked = $postData['isWorked'] ?? '';
        if ($isWorked !== '') {
            $model->isWorked = $isWorked === '1';
        }

        $model->pagingSearch->pageIndex = $pageIndex;
        $model->pagingSearch->pageSize = 50;
        $model->pagingSearch->sortField = $postData['sortField'] ?? '';
        $model->pagingSearch->sortDirection = $postData['sortDirection'] ?? '';

        $api = ClaimRevApi::makeFromGlobals();
        $raw = $api->searchPaymentInfo($model);

        $results = [];
        $rawResults = $raw['results'] ?? null;
        if (is_array($rawResults)) {
            foreach ($rawResults as $entry) {
                if (is_array($entry)) {
                    $results[] = self::normalizeAdvice($entry);
                }
            }
        }

        return [
            'results' => $results,
            'totalRecords' => TypeCoerce::asInt($raw['totalRecords'] ?? 0),
        ];
    }

    /**
     * Fetch a single payment advice aggregation by id.
     *
     * Returns the raw API entry (the same shape PaymentAdvicePostingService
     * consumes), or null when the id is unknown / not visible to the current
     * account. Used by the post endpoint to re-fetch authoritative payment
     * data instead of trusting the browser-supplied JSON.
     *
     * @return array<string, mixed>|null
     */
    public static function getPaymentAdviceById(string $paymentAdviceId): ?array
    {
        if ($paymentAdviceId === '') {
            return null;
        }

        $model = new PaymentAdviceSearchModel();
        $model->paymentAdviceId = $paymentAdviceId;
        $model->pagingSearch->pageIndex = 0;
        $model->pagingSearch->pageSize = 1;

        $api = ClaimRevApi::makeFromGlobals();
        $raw = $api->searchPaymentInfo($model);

        $rawResults = $raw['results'] ?? null;
        if (!is_array($rawResults)) {
            return null;
        }
        foreach ($rawResults as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            // Defensive: confirm the returned row matches the requested id.
            // The server-side filter is authoritative; doubling up here means a
            // bug in the filter can never let a cross-advice row through.
            if (TypeCoerce::asString($entry['paymentAdviceId'] ?? '') === $paymentAdviceId) {
                /** @var array<string, mixed> $entry */
                return $entry;
            }
        }
        return null;
    }

    /**
     * Coerce a raw API response entry into a fully-populated PaymentAdviceShape
     * with all keys present. Keeps a single shape across the producer and the
     * mock service so consumers don't have to deal with optional offsets.
     *
     * @param array<int|string, mixed> $entry
     * @return PaymentAdviceShape
     */
    public static function normalizeAdvice(array $entry): array
    {
        $piRaw = $entry['paymentInfo'] ?? null;
        $piArr = is_array($piRaw) ? $piRaw : [];
        $ciRaw = $entry['checkInformation'] ?? null;
        $ciArr = is_array($ciRaw) ? $ciRaw : [];

        return [
            'paymentAdviceId' => TypeCoerce::asString($entry['paymentAdviceId'] ?? ''),
            'receivedDate' => TypeCoerce::asString($entry['receivedDate'] ?? ''),
            'payerName' => TypeCoerce::asString($entry['payerName'] ?? ''),
            'payerNumber' => TypeCoerce::asString($entry['payerNumber'] ?? ''),
            'eraClassification' => TypeCoerce::asString($entry['eraClassification'] ?? ''),
            'paymentInfo' => [
                'patientFirstName' => TypeCoerce::asString($piArr['patientFirstName'] ?? ''),
                'patientLastName' => TypeCoerce::asString($piArr['patientLastName'] ?? ''),
                'patientControlNumber' => TypeCoerce::asString($piArr['patientControlNumber'] ?? ''),
                'claimStatusCode' => TypeCoerce::asString($piArr['claimStatusCode'] ?? ''),
                'totalClaimAmount' => TypeCoerce::asFloat($piArr['totalClaimAmount'] ?? 0),
                'claimPaymentAmount' => TypeCoerce::asFloat($piArr['claimPaymentAmount'] ?? 0),
                'patientResponsibility' => TypeCoerce::asFloat($piArr['patientResponsibility'] ?? 0),
                'isWorked' => TypeCoerce::asBool($piArr['isWorked'] ?? false),
            ],
            'checkInformation' => [
                'checkNumber' => TypeCoerce::asString($ciArr['checkNumber'] ?? ''),
                'checkDate' => TypeCoerce::asString($ciArr['checkDate'] ?? ''),
                'paymentMethodCode' => TypeCoerce::asString($ciArr['paymentMethodCode'] ?? ''),
                'paymentAmount' => TypeCoerce::asFloat($ciArr['paymentAmount'] ?? 0),
            ],
        ];
    }

    /**
     * Look up OpenEMR claim status for a patient control number.
     *
     * The patient control number from ClaimRev is formatted as "pid-encounter".
     *
     * @return array{pid: int, encounter: int, status: int, status_label: string}|null
     */
    public static function getOpenEmrClaimStatus(string $patientControlNumber): ?array
    {
        if ($patientControlNumber === '') {
            return null;
        }

        $parts = preg_split('/[\s\-]/', $patientControlNumber);
        if (!is_array($parts) || count($parts) < 2) {
            return null;
        }

        $pid = (int) $parts[0];
        $encounter = (int) $parts[1];

        if ($pid <= 0 || $encounter <= 0) {
            return null;
        }

        $row = QueryUtils::querySingleRow(
            "SELECT status, bill_process FROM claims WHERE patient_id = ? AND encounter_id = ? ORDER BY version DESC LIMIT 1",
            [$pid, $encounter]
        );

        if ($row === [] || $row === false) {
            return [
                'pid' => $pid,
                'encounter' => $encounter,
                'status' => -1,
                'status_label' => 'Not Found',
            ];
        }

        $status = TypeCoerce::asInt($row['status'] ?? 0);
        $labels = [
            0 => 'Not Billed',
            1 => 'Unbilled',
            2 => 'Billed',
            3 => 'Processed',
            6 => 'Crossover',
            7 => 'Denied',
        ];

        return [
            'pid' => $pid,
            'encounter' => $encounter,
            'status' => $status,
            'status_label' => $labels[$status] ?? 'Unknown (' . $status . ')',
        ];
    }
}
