<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Webhooks;

use OpenEMR\PaymentProcessing\Recorder;

class RecordPayment implements ProcessorInterface
{
    public function getEventTypes(): array
    {
        return [
            'payin.authorized',
        ];
    }

    public function handle(Webhook $webhook): void
    {
        assert($webhook->eventType === 'payment.something');

        $data = $webhook->data;

        // This SHOULD delegate to a payment service, but creating that too is
        // probably excessive scope creep.
        // TODO:
        // insert into ar_session
        // insert into ar_activity
        // update onsite_portal_activity
        // more?
        //
        // FROM WHAT I CAN TELL recording the payment is enough
        $r = new Recorder();
        $r->recordActivity([
            'patientId' => '', // Need to send in and retrieve from pmt metadata
            'encounterId' => '', // ^^
            'codeType' => 'PCP',
            'code' => '99205', // WHERE DOES THIS COME FROM
            'modifier' => '',
            'payerType' => '0',
            'postUser' => '????',
            'sessionId' => '????',
            'payAmount' => 'dollar-format-from-wh', // data.amount
            'adjustmentAmount' => '0.00',
            'memo' => 'Rainforest transaction id XXXX', // data.payin_id
        ]);
    }
}
