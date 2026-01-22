<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest;

use JsonSerializable;
use Money\Money;

/**
 * Data exchange class for dealing with Rainforest metadata payloads and
 * translating back into OpenEMR payment info
 */
readonly class EncounterData implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $code,
        public string $codeType,
        public Money $amount,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'codeType' => $this->codeType,
            'amount' => $this->amount,
        ];
    }
}
