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
 * Copyright (C) 2013 EMR Direct <http://www.emrdirect.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <http://www.emrdirect.com/>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

//ajax param should be set by calling ajax scripts
$isAjaxCall = isset($_POST['ajax']);

//if false, we may assume this is a cron job and set up accordingly
if (!$isAjaxCall) {
   $ignoreAuth = 1; 
   //process optional arguments when called from cron
   $_GET['site'] = (isset($argv[1])) ? $argv[1] : 'default';
   if (isset($argv[2]) && $argv[2]!='all') $_GET['background_service'] = $argv[2];
   if (isset($argv[3]) && $argv[3]=='1') $_GET['background_force'] = 1;
}

//an additional require file can be specified for each service in the background_services table
require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../sql.inc");

//Remove time limit so script doesn't time out
set_time_limit(0);

//Release session lock to prevent freezing of other scripts
session_write_close();

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

function execute_background_service_calls() {
  /**
   * Note: The global $service_name below is set to the name of the service currently being 
   * processed before the actual service function call, and is unset after normal
   * completion of the loop. If the script exits abnormally, the shutdown_function
   * uses the value of $service_name to do any required clean up.
   */
  global $service_name;

  $single_service = (isset($_GET['background_service']) ? $_GET['background_service'] : 
	(isset($_POST['background_service']) ? $_POST['background_service'] : ''));
  $force = ($_GET['background_force'] || $_POST['background_force']);

  $sql = 'SELECT * FROM background_services WHERE ' . ($force ? '1' : 'execute_interval > 0');
  if ($single_service!="")
    $services = sqlStatementNoLog($sql.' AND name=?',array($single_service));
  else
    $services = sqlStatementNoLog($sql.' ORDER BY sort_order');

  while($service = sqlFetchArray($services)){
    $service_name = $service['name'];
    if(!$service['active'] || $service['running'] == 1) continue;
    $interval=(int)$service['execute_interval'];

    //leverage locking built-in to UPDATE to prevent race conditions
    //will need to assess performance in high concurrency setting at some point
    $sql='UPDATE background_services SET running = 1, next_run = NOW()+ INTERVAL ?'
	. ' MINUTE WHERE running < 1 ' . ($force ? '' : 'AND NOW() > next_run ') . 'AND name = ?';
    if(sqlStatementNoLog($sql,array($interval,$service_name))===FALSE) continue;
    $acquiredLock =  mysql_affected_rows($GLOBALS['dbh']);
    if($acquiredLock<1) continue; //service is already running or not due yet

    if ($service['require_once'])
	require_once($GLOBALS['fileroot'] . $service['require_once']);
    if (!function_exists($service['function'])) continue;

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

function background_shutdown() {
  global $service_name;
  if (isset($service_name)) {
    
    $sql = 'UPDATE background_services SET running = 0 WHERE name = ?';
    $res = sqlStatementNoLog($sql, array($service_name));

  }
}

register_shutdown_function(background_shutdown);
execute_background_service_calls();
unset($service_name);

?>
