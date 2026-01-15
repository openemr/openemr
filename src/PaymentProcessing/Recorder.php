<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing;

use OpenEMR\Common\Database\QueryUtils;

class Recorder
{
    private function recordActivity(string $patientId, string $encounterId): void
    {
        $query = <<<'SQL'
            INSERT INTO `ar_activity`
            SET
                `pid` = ?,
                `encounter` = ?,
                `sequence_no` = ?,
                `code_type` = ?,
                `code` = ?,
                `modifier` = ?,
                `payer_type` = ?,
                `post_time` = ?,
                `post_user` = ?,
                `session_id` = ?,
                `modified_time` = ?,
                `pay_amount` = ?,
                `adj_amount` = ?,
                `memo` = ?,
                `account_code` = ?
            SQL;
        QueryUtils::inTransaction(function () {
            $next = $this->getNextSequenceNumber();
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
