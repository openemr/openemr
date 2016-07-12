<?php
/** 
 * forms/eye_mag/taskman.php
 * 
 * This file is the gateway to a practice's fax server.
 * It uses an email fax gateway that is behind the corporate
 * firewall, thus it is HIPPA compliant (at least TO the fax machine)
 * 
 * Copyright (C) 2016 Raymond Magauran <magauran@MedFetch.com> 
 * 
 * LICENSE: This program is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation; either version 3 
 * of the License, or (at your option) any later version. 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
 * GNU General Public License for more details. 
 * You should have received a copy of the GNU General Public License 
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;. 
 * 
 * @package OpenEMR 
 * @author Ray Magauran <magauran@MedFetch.com> 
 * @link http://www.open-emr.org 
 *   
 */

//error_reporting(0);

$fake_register_globals=false;
$sanitize_all_escapes=true;

$form_name = "eye_mag";
$form_folder="eye_mag";
// larry :: hack add for command line version
if (!$_SERVER['REQUEST_URI']) $_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
if (!$_SERVER['SERVER_NAME']) $_SERVER['SERVER_NAME']='localhost';
if (!$_SERVER['HTTP_HOST']) $_SERVER['HTTP_HOST']='default'; //need to figure out how to do this for non-default installs
$ignoreAuth=1;

require_once("../../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/html2pdf/vendor/autoload.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("php/".$form_name."_functions.php");
require_once("$srcdir/formatting.inc.php");
require_once($srcdir . "/../controllers/C_Document.class.php");
require_once($srcdir . "/documents.php");

require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/classes/Note.class.php");
require_once("$srcdir/htmlspecialchars.inc.php");
require_once("$srcdir/html2pdf/html2pdf.class.php");
require_once("php/taskman_functions.php");
require_once("report.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/classes/postmaster.php");



/**
 *
 *  Script to fax something to someone somewhere.
 *  This is currently set up for an in house secure email-fax gateway.
 *  This will need to be modified to use a HylaFax server if that is what you use.
 *
 *  We will need these as variables then:
 *		From, To, Object(s) to send
 *		These values are already in the openEMR DB, we just have to put them together correctly.
 *
 *  The first use case scenario is to fax the report of today's visit to a PCP/Referring doctor.
 *	The second scenario is the creation (or re-creation) of a Report of the encounter.
 *  To lighten loads, consider breaking these tasks up into separate tasks via cron or even using a different server
 *  to process these tasks, if in a multi-server environment.  Or run this file with openEMR's "background_services".
 *	Use a new table (form_taskman) to delineate this process.
 *  	1.  Create the Task to be performed: send it to DB table from the browser.
 *		2.  Cron job to scour this table, performing tasks as loads allow (check server load? <-- not implemented)
 *      3.  If the Object is ready to be created, create it. (e-signed required? <-- not implemented)
 *		4.  If the Object is created and it is a Report, Flag DB done (completed =1).
 *		5.  If the Object is created and it is a Fax, send it, and Flag DB done.
 *      
 */
global $encounter;
global $pid;
global $visit_date;
global $PDF_OUTPUT;
global $form_id;
global $task;
global $send;

$PDF_OUTPUT='1';
// If this is a request to make a task, make it.
$ajax_req = $_REQUEST;

if ($_REQUEST['action']=='make_task') make_task($ajax_req);
if ($_REQUEST['action']=='show_task') show_task($ajax_req);

// Get the list of Tasks and process them one-by-one
// unless this is a call from the web, then just do the task at hand
// or should the web not do these at all, leave them to the background processor?

 
$query  = "SELECT * FROM form_taskman where PATIENT_ID=? AND (COMPLETED is NULL or COMPLETED != '1')  order by REQ_DATE";
$result = sqlStatement($query,array($ajax_req['pid'])); 
while ($task= sqlFetchArray($result))   {
	$send = process_tasks($task);
	if ($_REQUEST['action']=='make_task') {
		echo json_encode($send);
		exit;
	}
}
$send['comments'] = "Nothing new to do!";
echo json_encode($send);
		exit;

?>

