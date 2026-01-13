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
        // This SHOULD delegate to a payment service, but creating that too is
        // probably excessive scope creep.
        // TODO:
        // insert into ar_session
        // insert into ar_activity
        // update onsite_portal_activity
        // more?
    }
}
