<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Webhooks;

use Money\{
    Currencies\ISOCurrencies,
    Formatter\DecimalMoneyFormatter,
};
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

        $data = $webhook->data;

        $metadata = Metadata::fromParsedJson($data['metadata']);
        $patientId = $metadata->patientId;

        $memo = sprintf('Rainforest transaction id %s', $data['payin_id']);
        $dmf = new DecimalMoneyFormatter(new ISOCurrencies());

        // See format $payinPayload in PaymentProcessing/Rainforest
        $activities = array_map(function ($enc) use ($dmf, $patientId, $memo): array {
            return [
                'patientId' => $patientId,
                'encounterId' => $enc->id,
                'codeType' => $enc->codeType,
                'code' => $enc->code,
                'modifier' => '',
                'payerType' => '0',
                'postUser' => '', // does this need to be filled?
                'sessionId' => '????', // ar_sessions
                'payAmount' => $dmf->format($enc->amount),
                'adjustmentAmount' => '0.00',
                'memo' => $memo,
            ];
        }, $metadata->encounters);

        //
        // FROM WHAT I CAN TELL recording the payment is enough but ar_session
        // seems relevant
        $r = new Recorder();
        // In txn?
        foreach ($activities as $activity) {
            $r->recordActivity($activity);
        }
        // insert into payments ?
        // insert into ar_session
        // update onsite_portal_activity
        // more?
    }
}
