<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Webhooks;

interface ProcessorInterface
{
    public function handle(Webhook $webhook): void;
}
