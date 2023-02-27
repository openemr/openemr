<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
// ALL ERRORS ROUTED TO THE LOG AND DISPLAY

error_reporting(E_ALL ^ E_NOTICE);
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors',1);

// OEMR SIGN ON NOT REQUIRED
$ignoreAuth = true;

if(defined('STDIN')) {
	parse_str(implode('&', array_slice($argv,1)), $_GET);
}

$SITE = ($_SESSION['site_id']) ? $_SESSION['site_id'] : $_GET['site'];
$SITE = ($SITE) ? $SITE : 'default';

// $FROM = $_GET['from'];
// $THRU = $_GET['thru'];

$hold = false;
if(isset($_GET['hold'])) { $hold = $_GET['hold']; }

$here = dirname(dirname(dirname(__FILE__)));

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/approve.inc');

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$start_date= date('Y-m-d', $last_month);
$end_date = fixDate(date('Y-m-d'), date('Y-m-d'));

if(isset($_GET['from'])) { $start_date = $_GET['from']; }
if(isset($_GET['thru'])) { $end_date = $_GET['thru']; }

print "We are here: $here\n";
print "Start Date: $start_date\n";
print "End Date: $end_date\n";
$_POST['form_from_date'] = $start_date;
$_POST['form_to_date'] = $end_date;
$_POST['form_provider'] = 114;
$_POST['form_status'] = 'i';
if($hold) { $_POST['hold'] = $hold; }
print "Hold: $hold\n";

ob_start();

// CALL THE PROGRAM HERE
include("$here/reports/myreports/cron_form_approve.php");


$output = ob_get_clean();

$status = file_put_contents('/home/richg/auto_approve.log', $output);
if($status === false) die('Failed to Create Log');

print "Wrote $status bytes to the auto_approve.log...\n\n";

exit;

