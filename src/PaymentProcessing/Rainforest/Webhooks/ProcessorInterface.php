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

/**
 * Webhook event processor
 *
 * A processor can handle one or more events, and there can be zero or more
 * processors that are registered for a given event.
 *
 * Each processor must be idempotent, and must not rely on running in
 * a specific order.
 */
interface ProcessorInterface
{
    /**
     * @return string[]
     */
    public function getEventTypes(): array;

    public function handle(Webhook $webhook): void;
}
