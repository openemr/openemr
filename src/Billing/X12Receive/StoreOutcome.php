<?php

/**
 * The result of storing one received file on the site.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\X12Receive;

final readonly class StoreOutcome
{
    public function __construct(
        public ReceivedStatus $status,
        public string $storedName = '',
        public string $message = '',
    ) {
    }
}
