<?php

/**
 * Writes laboratory results: a procedure_result row on an order line, and the
 * procedure_report it hangs off.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\ProcedureResultValidator;
use OpenEMR\Validators\ProcessingResult;

/**
 * Until now results were written by the results screen (interface/orders/orders_results.php), the
 * HL7 import, document import (C_Document) and C-CDA import (CdaTemplateImportDispose), none of
 * them through a service. This is the service-layer writer the FHIR Observation write uses (#14343).
 *
 * Every write runs in one transaction that starts by locking the order row, the lock
 * ProcedureService::updateOrder() takes before it renumbers an order's lines. So the line a
 * result is matched to cannot be renumbered under it, and two results posted at once for the
 * same line find or create the same report.
 *
 * Validation messages are keyed by the FHIR element a client sent, as ProcedureService's order
 * writes key theirs, since the FHIR write is the caller.
 *
 * @phpstan-type ResultRow array{result_data_type: string, result_code: string, result_text: string, date: ?string, units: string, result: string, range: string, abnormal: string, comments: string, result_status: string}
 * @phpstan-type ResultChanges array{result_data_type: string, units: string, result: string, range: string, abnormal: string, comments: string, result_status: string}
 */
final class ProcedureResultService extends BaseService
{
    public const TABLE_NAME = 'procedure_result';

    private const ORDER_NOT_FOUND = 'Observation.basedOn must reference one active ServiceRequest (procedure order) for the patient in Observation.subject';

    public function __construct(
        private readonly ProcedureResultValidator $validator = new ProcedureResultValidator(),
        private readonly UuidRegistry $reportUuids = new UuidRegistry(['table_name' => 'procedure_report', 'table_id' => 'procedure_report_id']),
        private readonly UuidRegistry $resultUuids = new UuidRegistry(['table_name' => 'procedure_result', 'table_id' => 'procedure_result_id']),
    ) {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * Writes one result on the order line its code names.
     *
     * The line's latest report is reused while nobody has reviewed it and it has the same report
     * date. Once a clinician has signed it, a new report is made instead, so a result added later
     * is reviewed in its own right rather than appearing under a signature given before it
     * existed. A result reported at another time gets its own report too, since the lab read
     * serves a result's effectiveDateTime from its report's date.
     *
     * @param int $orderId procedure_order.procedure_order_id
     * @param int $patientId The pid the result is for; the order must be this patient's.
     * @param ?int $encounterId When given, the encounter the order must belong to.
     * @param list<string> $codes The result's codes; the lowest-numbered order line whose code is one of them takes the result.
     * @param ResultRow $result The procedure_result columns. An empty result_code or result_text is filled from the matched line.
     * @param ?string $dateReport procedure_report.date_report: the report reused must have it, and a report created gets it.
     * @param int $sourceUserId users.id of whoever wrote it, for procedure_report.source.
     * @return ProcessingResult One row: uuid, procedure_result_id, procedure_report_id, report_created.
     */
    public function insertResult(
        int $orderId,
        int $patientId,
        ?int $encounterId,
        array $codes,
        array $result,
        ?string $dateReport,
        int $sourceUserId,
    ): ProcessingResult {
        $errors = $this->validator->validate(array_diff_key($result, ['result_code' => true, 'result_text' => true]));
        if ($errors !== []) {
            return self::invalid($errors);
        }

        return QueryUtils::inTransaction(function () use ($orderId, $patientId, $encounterId, $codes, $result, $dateReport, $sourceUserId): ProcessingResult {
            $order = $this->lockLiveOrder($orderId, $patientId);
            if ($order === null) {
                return self::invalid(['basedOn' => self::ORDER_NOT_FOUND]);
            }
            if ($encounterId !== null && $order['encounter_id'] !== $encounterId) {
                return self::invalid([
                    'encounter' => 'Observation.encounter must be the encounter of the order in Observation.basedOn',
                ]);
            }

            $line = $this->matchOrderLine($orderId, $codes);
            if ($line === null) {
                return self::invalid([
                    'code' => 'Observation.code "' . implode('", "', $codes)
                        . '" is not the code of a test on the order in Observation.basedOn',
                ]);
            }
            $result['result_code'] = $line['code'];
            if ($result['result_text'] === '') {
                $result['result_text'] = $line['name'] !== '' ? $line['name'] : $line['code'];
            }
            $errors = $this->validator->validate($result);
            if ($errors !== []) {
                return self::invalid($errors);
            }

            $reportCreated = false;
            $reportId = $this->reusableReportId($orderId, $line['seq'], $dateReport);
            if ($reportId === null) {
                $reportId = QueryUtils::sqlInsert(
                    'INSERT INTO `procedure_report` (`uuid`, `procedure_order_id`, `procedure_order_seq`,'
                    . ' `date_collected`, `date_report`, `source`, `review_status`) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [
                        $this->reportUuids->createUuid(),
                        $orderId,
                        $line['seq'],
                        $result['date'],
                        $dateReport,
                        $sourceUserId,
                        ProcedureReportReviewStatus::Received->value,
                    ]
                );
                $reportCreated = true;
            }

            $resultUuid = $this->resultUuids->createUuid();
            $resultId = QueryUtils::sqlInsert(
                'INSERT INTO `procedure_result` (`uuid`, `procedure_report_id`, `result_data_type`, `result_code`,'
                . ' `result_text`, `date`, `units`, `result`, `range`, `abnormal`, `comments`, `result_status`)'
                . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $resultUuid,
                    $reportId,
                    $result['result_data_type'],
                    $result['result_code'],
                    $result['result_text'],
                    $result['date'],
                    $result['units'],
                    $result['result'],
                    $result['range'],
                    $result['abnormal'],
                    $result['comments'],
                    $result['result_status'],
                ]
            );

            $processingResult = new ProcessingResult();
            $processingResult->addData([
                'uuid' => UuidRegistry::uuidToString($resultUuid),
                'procedure_result_id' => $resultId,
                'procedure_report_id' => $reportId,
                'report_created' => $reportCreated,
            ]);
            return $processingResult;
        });
    }

    /**
     * Changes the value, units, status, range, interpretation and comments of one result.
     *
     * The order, the line, the code and the patient a result was written under cannot change,
     * and neither can its dates: a write that names others is refused, not applied. A change to a
     * result whose report a clinician has already reviewed sends that report back to review,
     * since the signature was given to the values being replaced; a write that changes nothing
     * leaves the row and the signature alone.
     *
     * @param string $resultUuid procedure_result.uuid, as a string.
     * @param int $orderId The order the write names; it must be the one the result is on.
     * @param int $patientId The pid the write names; it must be the order's.
     * @param ?int $encounterId When given, the encounter the result's order must belong to.
     * @param list<string> $codes The result's codes; one of them must be the stored result_code.
     * @param ?string $effective When given, must be the result's date or its report's date_report.
     * @param ?string $issued When given, must be its report's date_report.
     * @param ResultChanges $changes
     * @return ProcessingResult One row: uuid, changed, review_reopened.
     */
    public function updateResult(
        string $resultUuid,
        int $orderId,
        int $patientId,
        ?int $encounterId,
        array $codes,
        ?string $effective,
        ?string $issued,
        array $changes,
    ): ProcessingResult {
        $notFound = ['uuid' => 'Observation not found for that id'];
        if (!UuidRegistry::isValidStringUUID($resultUuid)) {
            return self::invalid($notFound);
        }
        $errors = $this->validator->validate($changes);
        if ($errors !== []) {
            return self::invalid($errors);
        }

        $uuidBytes = UuidRegistry::uuidToBytes($resultUuid);
        $storedOrderId = $this->orderIdForResult($uuidBytes);
        if ($storedOrderId === null) {
            return self::invalid($notFound);
        }

        return QueryUtils::inTransaction(function () use ($uuidBytes, $resultUuid, $patientId, $orderId, $encounterId, $storedOrderId, $codes, $effective, $issued, $changes, $notFound): ProcessingResult {
            // The order row first, as insertResult() and ProcedureService::updateOrder() take it,
            // then the result read again under it. An order deleted since (inactive, or its form
            // deleted, which hides it on the results screen) takes no more changes.
            if ($this->lockLiveOrder($storedOrderId, $patientId) === null) {
                return self::invalid($notFound);
            }
            $row = QueryUtils::querySingleRow(
                'SELECT res.`procedure_result_id`, res.`result_code`, res.`date`, res.`result_data_type`, res.`units`,'
                . ' res.`result`, res.`range`, res.`abnormal`, res.`comments`, res.`result_status`,'
                . ' rep.`procedure_report_id`, rep.`date_report`, rep.`review_status`,'
                . ' po.`procedure_order_id`, po.`patient_id`, po.`encounter_id`'
                . ' FROM `procedure_result` res'
                . ' JOIN `procedure_report` rep ON rep.`procedure_report_id` = res.`procedure_report_id`'
                . ' JOIN `procedure_order` po ON po.`procedure_order_id` = rep.`procedure_order_id`'
                . ' WHERE res.`uuid` = ?',
                [$uuidBytes]
            );
            if (!is_array($row) || self::intOrNull($row['patient_id'] ?? null) !== $patientId) {
                // Another patient's result answers the same as a missing one.
                return self::invalid($notFound);
            }
            if (self::intOrNull($row['procedure_order_id'] ?? null) !== $orderId) {
                return self::invalid([
                    'basedOn' => 'Observation.basedOn must reference the order this result was written under',
                ]);
            }
            if ($encounterId !== null && (self::intOrNull($row['encounter_id'] ?? null) ?? 0) !== $encounterId) {
                return self::invalid([
                    'encounter' => 'Observation.encounter must be the encounter of the order in Observation.basedOn',
                ]);
            }
            $storedCode = is_string($row['result_code'] ?? null) ? $row['result_code'] : '';
            if (!in_array($storedCode, $codes, true)) {
                return self::invalid([
                    'code' => 'Observation.code must keep the code this result was written under ("' . $storedCode . '")',
                ]);
            }
            $storedDate = is_string($row['date'] ?? null) ? $row['date'] : null;
            $storedReportDate = is_string($row['date_report'] ?? null) ? $row['date_report'] : null;
            if ($effective !== null && $effective !== $storedDate && $effective !== $storedReportDate) {
                return self::invalid([
                    'effectiveDateTime' => 'Observation.effectiveDateTime is set when the result is created and cannot be changed',
                ]);
            }
            if ($issued !== null && $issued !== $storedReportDate) {
                return self::invalid([
                    'issued' => 'Observation.issued is the report date, set when the report is created, and cannot be changed',
                ]);
            }

            // A body sent back unchanged (a retry, or a client syncing what it read) is not a change,
            // so it leaves a signed report signed.
            $changed = false;
            foreach ($changes as $column => $value) {
                $stored = $row[$column] ?? null;
                if ((is_string($stored) ? $stored : '') !== $value) {
                    $changed = true;
                    break;
                }
            }
            if (!$changed) {
                $processingResult = new ProcessingResult();
                $processingResult->addData(['uuid' => $resultUuid, 'changed' => false, 'review_reopened' => false]);
                return $processingResult;
            }

            $resultId = self::intOrNull($row['procedure_result_id'] ?? null);
            $reportId = self::intOrNull($row['procedure_report_id'] ?? null);
            QueryUtils::sqlStatementThrowException(
                'UPDATE `procedure_result` SET `result_data_type` = ?, `units` = ?, `result` = ?, `range` = ?,'
                . ' `abnormal` = ?, `comments` = ?, `result_status` = ? WHERE `procedure_result_id` = ?',
                [
                    $changes['result_data_type'],
                    $changes['units'],
                    $changes['result'],
                    $changes['range'],
                    $changes['abnormal'],
                    $changes['comments'],
                    $changes['result_status'],
                    $resultId,
                ]
            );

            $reviewReopened = ($row['review_status'] ?? null) === ProcedureReportReviewStatus::Reviewed->value;
            if ($reviewReopened) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `procedure_report` SET `review_status` = ? WHERE `procedure_report_id` = ?',
                    [ProcedureReportReviewStatus::Received->value, $reportId]
                );
            }

            $processingResult = new ProcessingResult();
            $processingResult->addData(['uuid' => $resultUuid, 'changed' => true, 'review_reopened' => $reviewReopened]);
            return $processingResult;
        });
    }

    /**
     * Locks an order that results can be written on: active, its encounter form (when it has
     * one) not deleted, and the given patient's. The results screen hides an order whose form
     * was deleted, so a result written there would be lost to it.
     *
     * @return ?array{encounter_id: int}
     */
    private function lockLiveOrder(int $orderId, int $patientId): ?array
    {
        $order = QueryUtils::querySingleRow(
            'SELECT `patient_id`, `encounter_id`, `activity` FROM `procedure_order`'
            . ' WHERE `procedure_order_id` = ? FOR UPDATE',
            [$orderId]
        );
        if (!is_array($order)) {
            return null;
        }
        if (self::intOrNull($order['activity'] ?? null) !== 1 || self::intOrNull($order['patient_id'] ?? null) !== $patientId) {
            return null;
        }
        $deletedForms = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS `deleted_forms` FROM `forms` WHERE `form_id` = ? AND `pid` = ?"
            . " AND `formdir` = 'procedure_order' AND `deleted` <> 0",
            'deleted_forms',
            [$orderId, $patientId]
        );
        if (self::intOrNull($deletedForms) !== 0) {
            return null;
        }

        return ['encounter_id' => self::intOrNull($order['encounter_id'] ?? null) ?? 0];
    }

    /**
     * The order line a result belongs to: the lowest procedure_order_seq whose procedure_code
     * is one of the codes, bare or with a LOINC: prefix (both are stored, by the FHIR
     * ServiceRequest write and by the order screen). Compared in PHP, byte for byte, so the
     * column's case-folding collation cannot match a code that differs.
     *
     * @param list<string> $codes
     * @return ?array{seq: int, code: string, name: string}
     */
    private function matchOrderLine(int $orderId, array $codes): ?array
    {
        $lines = QueryUtils::fetchRecords(
            'SELECT `procedure_order_seq`, `procedure_code`, `procedure_name` FROM `procedure_order_code`'
            . ' WHERE `procedure_order_id` = ? ORDER BY `procedure_order_seq`',
            [$orderId]
        );
        foreach ($lines as $line) {
            $stored = is_string($line['procedure_code'] ?? null) ? trim($line['procedure_code']) : '';
            if (str_starts_with(strtoupper($stored), 'LOINC:')) {
                $stored = substr($stored, strlen('LOINC:'));
            }
            if ($stored === '' || !in_array($stored, $codes, true)) {
                continue;
            }
            $seq = self::intOrNull($line['procedure_order_seq'] ?? null);
            if ($seq === null) {
                continue;
            }
            return [
                'seq' => $seq,
                'code' => $stored,
                'name' => is_string($line['procedure_name'] ?? null) ? $line['procedure_name'] : '',
            ];
        }

        return null;
    }

    /**
     * The line's latest report, when nobody has reviewed it yet and it has this report date.
     */
    private function reusableReportId(int $orderId, int $seq, ?string $dateReport): ?int
    {
        $report = QueryUtils::querySingleRow(
            'SELECT `procedure_report_id`, `review_status`, `date_report` FROM `procedure_report`'
            . ' WHERE `procedure_order_id` = ? AND `procedure_order_seq` = ?'
            . ' ORDER BY `procedure_report_id` DESC LIMIT 1',
            [$orderId, $seq]
        );
        if (
            !is_array($report)
            || ($report['review_status'] ?? null) === ProcedureReportReviewStatus::Reviewed->value
            || ($report['date_report'] ?? null) !== $dateReport
        ) {
            return null;
        }

        return self::intOrNull($report['procedure_report_id'] ?? null);
    }

    private function orderIdForResult(string $uuidBytes): ?int
    {
        $orderId = QueryUtils::fetchSingleValue(
            'SELECT rep.`procedure_order_id` FROM `procedure_result` res'
            . ' JOIN `procedure_report` rep ON rep.`procedure_report_id` = res.`procedure_report_id`'
            . ' WHERE res.`uuid` = ?',
            'procedure_order_id',
            [$uuidBytes]
        );

        return self::intOrNull($orderId);
    }

    /**
     * An id column as it arrives: an int from a read with bound values, a numeric string
     * otherwise.
     */
    private static function intOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }

    /**
     * @param array<string, string> $messages
     */
    private static function invalid(array $messages): ProcessingResult
    {
        $result = new ProcessingResult();
        $result->setValidationMessages($messages);
        return $result;
    }
}
