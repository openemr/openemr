<?php

/**
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright (c) 2026 OpenCoreEMR, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @link      https://www.open-emr.org
 * @package   OpenEMR
 */

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest;

use JsonSerializable;
use Money\{
    Currency,
    Money,
};

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

    public static function fromParsedJson(array $data): EncounterData
    {
        return new EncounterData(
            id: $data['id'],
            code: $data['code'],
            codeType: $data['codeType'],
            amount: new Money(
                amount: $data['amount']['amount'],
                currency: new Currency($data['amount']['currency']),
            ),
        );
    }
}
