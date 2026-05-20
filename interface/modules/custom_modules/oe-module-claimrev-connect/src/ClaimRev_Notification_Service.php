<?php

/**
 * Background service that polls ClaimRev for account notifications
 * and creates pnotes in OpenEMR so users see them in their Messages inbox.
 *
 * Runs every 60 minutes. Tracks which notifications have already been
 * delivered via mod_claimrev_notifications to prevent duplicates.
 * Marks notifications as read on ClaimRev after delivery.
 *
 * @package OpenEMR
 * @link    http://www.claimrev.com
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Modules\ClaimRevConnector\NotificationPollService;

/**
 * Cron-registered entry point. Background service tables identify cron
 * targets by global function name, so this thin wrapper just delegates
 * to the namespaced NotificationPollService.
 *
 * @phpstan-ignore openemr.noGlobalNsFunctions
 */
function start_claimrev_notifications(): void
{
    NotificationPollService::run();
}
