<?php
/** 
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
session_start();
if( isset( $_SESSION['pid'] ) && isset( $_SESSION['patient_portal_onsite'] ) ){
	$pid = $_SESSION['pid'];
	$ignoreAuth = true;
	$fake_register_globals=false;
	$sanitize_all_escapes=true;
	require_once ( dirname( __FILE__ ) . "/../../interface/globals.php" );
} else{
	session_destroy();
	$ignoreAuth = false;
	$sanitize_all_escapes = true;
	$fake_register_globals = false;
	require_once ( dirname( __FILE__ ) . "/../../interface/globals.php" );
	if( ! isset( $_SESSION['authUserID'] ) ){
		$landingpage = "index.php";
		header( 'Location: ' . $landingpage );
		exit();
	}
}

require_once("./appsql.class.php");
//$_SESSION['whereto'] = 'paymentpanel';
if($_SESSION['portal_init'] != 'true') $_SESSION['whereto'] = 'paymentpanel';
$_SESSION['portal_init'] = false;

if ($_POST['mode'] == 'portal-save') {
	$form_pid = $_POST['form_pid'];
	$form_method = trim($_POST['form_method']);
	$form_source = trim($_POST['form_source']);
	$upay = isset($_POST['form_upay']) ? $_POST['form_upay'] : '';
	$cc = isset($_POST['extra_values']) ? $_POST['extra_values'] : '';
	$amts = isset($_POST['inv_values']) ? $_POST['inv_values'] : '';
	$s = SaveAudit( $form_pid, $amts, $cc );
	if($s) echo 'failed';
echo true;
}
else if ($_POST['mode'] == 'review-save') {
	$form_pid = $_POST['form_pid'];
	$form_method = trim($_POST['form_method']);
	$form_source = trim($_POST['form_source']);
	$upay = isset($_POST['form_upay']) ? $_POST['form_upay'] : '';
	$cc = isset($_POST['extra_values']) ? $_POST['extra_values'] : '';
	$amts = isset($_POST['inv_values']) ? $_POST['inv_values'] : '';
	$s = CloseAudit( $form_pid, $amts, $cc );
	if($s) echo 'failed';
echo true;
}
function SaveAudit( $pid, $amts, $cc ){
	$appsql = new ApplicationTable();
	try{
		$audit = Array ();
		$audit['patient_id'] = $pid;
		$audit['activity'] = "payment";
		$audit['require_audit'] = "1";
		$audit['pending_action'] = "review";
		$audit['action_taken'] = "";
		$audit['status'] = "waiting";
		$audit['narrative'] = "Authorize online payment.";
		$audit['table_action'] = '';
		$audit['table_args'] =  $amts;
		$audit['action_user'] = "0";
		$audit['action_taken_time'] = "";
		$audit['checksum'] = aes256Encrypt($cc);

		$edata = $appsql->getPortalAudit( $pid, 'review', 'payment' );
		$audit['date'] = $edata['date'];
		if( $edata['id'] > 0 ) $appsql->portalAudit( 'update', $edata['id'], $audit );
		else{
			$appsql->portalAudit( 'insert', '', $audit );
		}
	} catch( Exception $ex ){
		return $ex;
	}
	return 0;
}
function CloseAudit( $pid, $amts, $cc, $action='payment posted', $paction='notify patient'){
	$appsql = new ApplicationTable();
	try{
		$audit = Array ();
		$audit['patient_id'] = $pid;
		$audit['activity'] = "payment";
		$audit['require_audit'] = "1";
		$audit['pending_action'] = $paction;//'review';//
		$audit['action_taken'] = $action;
		$audit['status'] = "closed";//'waiting';
		$audit['narrative'] = "Payment authorized.";
		$audit['table_action'] = "update";
		$audit['table_args'] = $amts;
		$audit['action_user'] = isset( $_SESSION['authUserID'] ) ? $_SESSION['authUserID'] : "0";
		$audit['action_taken_time'] = date( "Y-m-d H:i:s" );
		$audit['checksum'] = aes256Encrypt($cc);

		$edata = $appsql->getPortalAudit( $pid, 'review', 'payment' );
		$audit['date'] = $edata['date'];
		if( $edata['id'] > 0 ) $appsql->portalAudit( 'update', $edata['id'], $audit );
	} catch( Exception $ex ){
		return $ex;
	}
	return 0;
}
function OnlinePayPost($type, $auditrec) { // start of port for payments
	$extra = json_decode($_POST['extra_values'], true);
	$form_pid = $_POST['form_pid'];
	$form_method = trim($_POST['form_method']);
	$form_source = trim($_POST['form_source']);
	$patdata = getPatientData($form_pid, 'fname,mname,lname,pubpid');
	$NameNew=$patdata['fname'] . " " .$patdata['lname']. " " .$patdata['mname'];
}
?>