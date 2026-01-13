<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Webhooks;

readonly class Webhook
{
    public array $data;
    public string $eventType;

    public function __construct(array $body)
    {
        $this->data = $body['data'];
        $this->eventType = $body['event_type'];
    }

    public function getMerchantId(): ?string
    {
        return $this->data['merchant_id'];
    }
}
