<?php

/**
 * Manage background operations that should be executed at intervals.
 *
 * This script may be executed by a suitable Ajax request, by a cron job, or both.
 *
 * When called from cron, optinal args are [site] [service] [force]
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

//ajax param should be set by calling ajax scripts
$isAjaxCall = isset($_POST['ajax']);

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

    // not calling from cron job so ensure passes csrf check
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

//Remove time limit so script doesn't time out
set_time_limit(0);

//Safety in case one of the background functions tries to output data
ignore_user_abort(1);

/**
 * Execute background services
 * This function reads a list of available services from the background_services table
 * For each service that is not already running and is due for execution, the associated
 * background function is run.
 *
 * Note: Each service must do its own logging, as appropriate, and should disable itself
 * to prevent continued service calls if an error condition occurs which requires
 * administrator intervention. Any service function return values and output are ignored.
 */

function execute_background_service_calls()
{
  /**
   * Note: The global $service_name below is set to the name of the service currently being
   * processed before the actual service function call, and is unset after normal
   * completion of the loop. If the script exits abnormally, the shutdown_function
   * uses the value of $service_name to do any required clean up.
   */
    global $service_name;

    $single_service = isset($_REQUEST['background_service']) ? $_REQUEST['background_service'] : '';
    $force = (isset($_REQUEST['background_force']) && $_REQUEST['background_force']);

    $sql = 'SELECT * FROM background_services WHERE ' . ($force ? '1' : 'execute_interval > 0');
    if ($single_service != "") {
        $services = sqlStatementNoLog($sql . ' AND name=?', array($single_service));
    } else {
        $services = sqlStatementNoLog($sql . ' ORDER BY sort_order');
    }

    while ($service = sqlFetchArray($services)) {
        $service_name = $service['name'];
        if (!$service['active'] || $service['running'] == 1) {
            continue;
        }

        $interval = (int)$service['execute_interval'];

        //leverage locking built-in to UPDATE to prevent race conditions
        //will need to assess performance in high concurrency setting at some point
        $sql = 'UPDATE background_services SET running = 1, next_run = NOW()+ INTERVAL ?'
        . ' MINUTE WHERE running < 1 ' . ($force ? '' : 'AND NOW() > next_run ') . 'AND name = ?';
        if (sqlStatementNoLog($sql, array($interval,$service_name)) === false) {
            continue;
        }

        $acquiredLock =  generic_sql_affected_rows();
        if ($acquiredLock < 1) {
            continue; //service is already running or not due yet
        }

        if ($service['require_once']) {
            require_once($GLOBALS['fileroot'] . $service['require_once']);
        }

        if (!function_exists($service['function'])) {
            continue;
        }

        //use try/catch in case service functions throw an unexpected Exception
        try {
            $service['function']();
        } catch (Exception $e) {
          //do nothing
        }

        $sql = 'UPDATE background_services SET running = 0 WHERE name = ?';
        $res = sqlStatementNoLog($sql, array($service_name));
    }
}

/**
 * Catch unexpected failures.
 *
 * if the global $service_name is still set, then a die() or exit() occurred during the execution
 * of that service's function call, and we did not complete the foreach loop properly,
 * so we need to reset the is_running flag for that service before quitting
 */

function background_shutdown()
{
    global $service_name;
    if (isset($service_name)) {
        $sql = 'UPDATE background_services SET running = 0 WHERE name = ?';
        $res = sqlStatementNoLog($sql, array($service_name));
    }
}

register_shutdown_function('background_shutdown');
execute_background_service_calls();
unset($service_name);
