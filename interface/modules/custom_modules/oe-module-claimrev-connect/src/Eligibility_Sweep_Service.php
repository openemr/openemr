<?php

/**
 * Cron-registered entry point for the eligibility sweep. Background service
 * tables identify cron targets by global function name, so this thin wrapper
 * just delegates to the namespaced EligibilitySweepService.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Modules\ClaimRevConnector\EligibilitySweepService;

/**
 * @phpstan-ignore openemr.noGlobalNsFunctions
 */
function start_eligibility_sweep(): void
{
    EligibilitySweepService::run();
}
