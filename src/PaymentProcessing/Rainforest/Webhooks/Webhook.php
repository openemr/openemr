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
