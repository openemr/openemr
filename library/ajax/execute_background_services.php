<?php

/**
 * Manage background operations that should be executed at intervals.
 *
 * This script may be executed by a suitable Ajax request, by a cron job, or both.
 *
 * When called from cron, optional args are [site] [service] [force]
 * @param site to specify a specific site, 'default' used if omitted
 * @param service to specify a specific service, 'all' used if omitted
 * @param force '1' to ignore specified wait interval, '0' to honor wait interval
 *
 * The same parameters can be accessed via Ajax using the $_POST variables
 * 'site', 'background_service', and 'background_force', respectively.
 *
 * For both calling methods, this script guarantees that each active
 * background service function: (1) will not be called again before it has completed,
 * and (2) will not be called any more frequently than at the specified interval
 * (unless the force execution flag is used).  A service function that is already running
 * will not be called a second time even if the force execution flag is used.
 *
 * Notes for the default background behavior:
 * 1. If the Ajax method is used, services will only be checked while
 * Ajax requests are being received, which is currently only when users are
 * logged in.
 * 2. All services are checked and called sequentially in the order specified
 * by the sort_order field in the background_services table. Service calls that are "slow"
 * should be given a higher sort_order value.
 * 3. The actual interval between two calls to a given background service may be
 * as long as the time to complete that service plus the interval between
 * n+1 calls to this script where n is the number of other services preceding it
 * in the array, even if the specified minimum interval is shorter, so plan
 * accordingly. Example: with a 5 min cron interval, the 4th service on the list
 * may not be started again for up to 20 minutes after it has completed if
 * services 1, 2, and 3 take more than 15, 10, and 5 minutes to complete,
 * respectively.
 *
 * Returns a count of due messages for current user.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    EMR Direct <https://www.emrdirect.com/>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 EMR Direct <https://www.emrdirect.com/>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\Background\BackgroundServiceRunner;

//ajax param should be set by calling ajax scripts
$isAjaxCall = filter_has_var(INPUT_POST, 'ajax');

//if false ajax and this is a called from command line, this is a cron job and set up accordingly
if (!$isAjaxCall && (php_sapi_name() === 'cli')) {
    $ignoreAuth = 1;
    //process optional arguments when called from cron
    $_GET['site'] = $argv[1] ?? 'default';
    if (isset($argv[2]) && $argv[2] != 'all') {
        $_GET['background_service'] = $argv[2];
    }

    if (isset($argv[3]) && $argv[3] == '1') {
        $_GET['background_force'] = 1;
    }

    //an additional require file can be specified for each service in the background_services table
    // Since from command line, set $sessionAllowWrite since need to set site_id session and no benefit to set to false
    $sessionAllowWrite = true;
    require_once(__DIR__ . "/../../interface/globals.php");
} else {
    //an additional require file can be specified for each service in the background_services table
    require_once(__DIR__ . "/../../interface/globals.php");

    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
}

//Remove time limit so script doesn't time out
set_time_limit(0);

//Safety in case one of the background functions tries to output data
ignore_user_abort(1);

$runner = new BackgroundServiceRunner();

if (!$isAjaxCall && (php_sapi_name() === 'cli')) {
    // CLI args were parsed into $_GET above for globals bootstrap; read directly from $argv
    $serviceName = (isset($argv[2]) && $argv[2] !== 'all') ? $argv[2] : null;
    $force = ($argv[3] ?? '0') === '1';
} else {
    $serviceName = filter_input(INPUT_POST, 'background_service');
    $force = filter_var(filter_input(INPUT_POST, 'background_force'), FILTER_VALIDATE_BOOLEAN);
}
$runner->run(is_string($serviceName) && $serviceName !== '' ? $serviceName : null, $force);
