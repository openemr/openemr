<?php

/**
 * Watchdog background service that resets stuck ClaimRev services.
 * This service runs every 20 minutes and checks if any ClaimRev
 * background services have been stuck in running state for more
 * than 10 minutes. If so, it resets them so they can run again.
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
use OpenEMR\Modules\ClaimRevConnector\ClaimRevModuleSetup;
use OpenEMR\Modules\ClaimRevConnector\GlobalConfig;

/**
 * @phpstan-ignore openemr.noGlobalNsFunctions
 */
function start_claimrev_watchdog(): void
{
    $enabled = OEGlobalsBag::getInstance()->get(GlobalConfig::CONFIG_ENABLE_WATCHDOG) ?? '1';
    if ($enabled !== false && $enabled !== '' && $enabled !== '0' && $enabled !== 0) {
        ClaimRevModuleSetup::resetStuckServices();
    }
}
