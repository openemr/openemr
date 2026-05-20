<?php

    /**
     * Executes the background service for billing, which sends EDI claims
     * directly to claimRev if enabled
     *
     * @package OpenEMR
     * @link    http://www.claimrev.com
     *
     * @author    Brad Sharp <brad.sharp@claimrev.com>
     * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
     * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
     */

declare(strict_types=1);

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ClaimRevConnector\EligibilityTransfer;

/**
 * @phpstan-ignore openemr.noGlobalNsFunctions
 */
function start_send_eligibility(): void
{
    $autoSend = OEGlobalsBag::getInstance()->get('oe_claimrev_send_eligibility') ?? null;
    if ($autoSend) {
        EligibilityTransfer::sendWaitingEligibility();
    }
}
