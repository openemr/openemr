<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing;

use OpenEMR\Common\Database\QueryUtils;

class Recorder
{
    /**
     * Future scope:
     * - Amounts to Money type
     * - Codes to constants/enums
     * - Improve timestamp handling (DB automatic or PSR Clock?)
     *
     * payerType seems to be a number in [0-3]
     *
     * @param array{
     *   patientId: string,
     *   encounterId: string,
     *   codeType: string,
     *   code: string,
     *   modifier: string,
     *   payerType: string,
     *   postUser: string,
     *   sessionId: string,
     *   payAmount: string,
     *   adjustmentAmount: string,
     *   memo: string,
     *   accountCode: string,
     *   followUp?: true,
     *   FollowUpReason?: string,
     * } $data
     */
    public function recordActivity(array $data): void
    {
        $query = <<<'SQL'
            INSERT INTO `ar_activity` (
                `pid`,
                `encounter`,
                `sequence_no`,
                `code_type`,
                `code`,
                `modifier`,
                `payer_type`,
                `post_time`,
                `post_user`,
                `session_id`,
                `modified_time`,
                `pay_amount`,
                `adj_amount`,
                `memo`,
                `account_code`,
                `follow_up`,
                `follow_up_note`
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);
            SQL;
        QueryUtils::inTransaction(function () use ($query, $data) {
            $now = date('Y-m-d H:i:s');
            $next = $this->getNextSequenceNumber(
                patientId: $data['patientId'],
                encounterId: $data['encounterId'],
            );
            // Sort into an order to match the query
            $params = [
                $data['patientId'],
                $data['encounterId'],
                $next,
                $data['codeType'],
                $data['code'],
                $data['modifier'],
                $data['payerType'],
                $now,
                $data['postUser'],
                $data['sessionId'],
                $now,
                $data['payAmount'],
                $data['adjustmentAmount'],
                $data['memo'],
                $data['accountCode'],
                $data['followUp'] ? 'y' : '',
                $data['followUpNote'] ?? '',
            ];
            sqlStatement($query, $params);
        });
    }

    // Note: values are stored as `int`s
    //
    // Note: even in a default-configured DB transaction, this still has
    // a potential race condition. It should either be done as a subquery in
    // the insert, or using a locking read (SELECT...FOR UPDATE may work?)
    private function getNextSequenceNumber(string $patientId, string $encounterId): string
    {
        $result = QueryUtils::querySingleRow(<<<'SQL'
            SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment
            FROM ar_activity
            WHERE pid = ? AND encounter = ?
        SQL, [$patientId, $encounterId]);
        return $result['increment'];
    }
}
