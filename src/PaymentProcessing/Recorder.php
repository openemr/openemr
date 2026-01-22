<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing;

use Money\{
    Currencies\ISOCurrencies,
    Formatter\DecimalMoneyFormatter,
    Money,
};
use OpenEMR\Common\Database\QueryUtils;

class Recorder
{
    // Future (all blocks): convert to enums
    // Future (all blocks): add other supported types

    public const ADJUSTMENT_CODE_PATIENT_PAYMENT = 'patient_payment';

    public const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';

    public const PAYMENT_TYPE_PATIENT = 'patient';

    // COPAY/PCP
    /**
     * @param array{
     *   payerId: string,
     *   userId: string,
     *   reference: string,
     *   payTotal: Money,
     *   paymentType: self::PAYMENT_TYPE_*,
     *   description: string,
     *   adjustmentCode: self::ADJUSTMENT_CODE_*,
     *   patientId: string,
     *   paymentMethod: self::PAYMENT_METHOD_*,
     * } $data
     * @return string the session identifier
     */
    public function createSession(array $data): string
    {
        $now = date('Y-m-d H:i:s');
        $date = date('Y-m-d'); // Future: param?
        $query = <<<'SQL'
        INSERT INTO `ar_session` (
            `payer_id`,
            `user_id`,
            `closed`,
            `reference`,
            `check_date`,
            `deposit_date`,
            `pay_total`,
            `created_time`,
            `modified_time`,
            `global_amount`,
            `payment_type`,
            `description`,
            `adjustment_code`,
            `post_to_date`,
            `patient_id`,
            `payment_method`
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        SQL;
        $params = [
            $data['payerId'], // `payer_id`
            $data['userId'], // `user_id`
            0, // `closed`
            $data['reference'], // `reference`
            $date, // `check_date`
            $date, // `deposit_date`
            self::formatMoney($data['payTotal']), // `pay_total`
            $now, // `created_time`
            $now, // `modified_time`
            '0.00', // `global_amount`
            $data['paymentType'], // `payment_type`
            $data['description'], // `description`
            $data['adjustmentCode'], // `adjustment_code`
            $date, // `post_to_date`
            $data['patientId'], // `patient_id`
            $data['paymentMethod'], // `payment_method
        ];
        return (string) QueryUtils::sqlInsert($query, $params);
    }

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
     *   memo?: string,
     *   accountCode?: string,
     *   followUp?: true,
     *   followUpNote?: string,
     *   reasonCode?: string,
     *   postDate?: string,
     *   payerClaimNumber?: string,
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
                `follow_up_note`,
                `reason_code`,
                `post_date`,
                `payer_claim_number`
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);
            SQL;
        QueryUtils::inTransaction(function () use ($query, $data): void {
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
                $data['memo'] ?? '',
                $data['accountCode'] ?? '',
                ($data['followUp'] ?? false) ? 'y' : '',
                $data['followUpNote'] ?? null,
                $data['reasonCode'] ?? null,
                $data['postDate'] ?? null,
                $data['payerClaimNumber'] ?? null,
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

    /**
     * Converts well-formed Money objects to a decimal string
     *
     * e.g. Money::USD(123) => '1.23'
     */
    private static function formatMoney(Money $money): string
    {
        // Note: this should be done in a DBAL layer, not here.
        $dmf = new DecimalMoneyFormatter(new ISOCurrencies());
        return $dmf->format($money);
    }
}
