<?php

/**
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright (c) 2026 OpenCoreEMR, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @link      https://www.open-emr.org
 * @package   OpenEMR
 */

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Webhooks;

use Money\{
    Currencies\ISOCurrencies,
    Currency,
    Formatter\DecimalMoneyFormatter,
    Money,
};
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\PaymentProcessing\Rainforest\Metadata;
use OpenEMR\PaymentProcessing\Recorder;

class RecordPayment implements ProcessorInterface
{
    public function getEventTypes(): array
    {
        return [
            'payin.authorized',
            // payin.completed
            // 'payin.processing',
            // 'payin.created',
        ];
    }

    public function handle(Webhook $webhook): void
    {
        // FIXME: this must be idempotent
        assert($webhook->eventType === 'payment.something');
        // This transaction should be done in a general recording service, but
        // PaymentProcessing/Recorder isn't sophisticaed enough yet.
        //
        // Also, most of the data munging should be done prior to DB
        // interactions, but the use() would be bonkers.
        QueryUtils::inTransaction(function () use ($webhook): void {

            /**
             * @var array{
             *   amount: int,
             *   currency_code: string,
             *   metadata: array<string, mixed>,
             *   payin_id: string,
             * }
             */
            $data = $webhook->data;

            $metadata = Metadata::fromParsedJson($data['metadata']);
            $patientId = $metadata->patientId;

            $memo = sprintf('Rainforest transaction id %s', $data['payin_id']);
            $dmf = new DecimalMoneyFormatter(new ISOCurrencies());

            $r = new Recorder();
            // (txn should start here)
            $sessionId = $r->createSession([
                'payerId' => '',
                'userId' => '',
                'reference' => $data['payin_id'],
                'payTotal' => new Money((string)$data['amount'], new Currency($data['currency_code'])),
                'paymentType' => Recorder::PAYMENT_TYPE_PATIENT,
                'description' => '',
                'adjustmentCode' => Recorder::ADJUSTMENT_CODE_PATIENT_PAYMENT,
                'patientId' => $patientId,
                'paymentMethod' => Recorder::PAYMENT_METHOD_CREDIT_CARD,
            ]);

            // See format $payinPayload in PaymentProcessing/Rainforest
            $activities = array_map(fn($enc): array => [
                'patientId' => $patientId,
                'encounterId' => $enc->id,
                'codeType' => $enc->codeType,
                'code' => $enc->code,
                'modifier' => '',
                'payerType' => '0',
                'postUser' => '', // does this need to be filled?
                'sessionId' => $sessionId,
                'payAmount' => $dmf->format($enc->amount),
                'adjustmentAmount' => '0.00',
                'memo' => $memo,
                'accountCode' => 'PP', // this and paymentType above different for copay?
            ], $metadata->encounters);

            //
            // FROM WHAT I CAN TELL recording the payment is enough but ar_session
            // seems relevant
            // In txn?
            foreach ($activities as $activity) {
                $r->recordActivity($activity);
            }
        });
        // insert into payments ?
        // insert into ar_session
        // update onsite_portal_activity
        // more?
    }
}
