<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing;

use OpenEMR\Common\Database\QueryUtils;

class Recorder
{
    private function recordActivity(string $patientId, string $encounterId): void
    {
    }

    // Note: values are stored as `int`s
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
