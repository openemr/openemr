<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Webhooks;

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
        // TODO:
        // insert into ar_session
        // insert into ar_activity
        // update onsite_portal_activity
        // more?
    }
}
