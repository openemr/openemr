<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest;

readonly class Webhook
{
    public function __construct(public array $data)
    {
    }
}
