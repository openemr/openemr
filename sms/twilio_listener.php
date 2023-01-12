<?php
/** **************************************************************************
 *	sms/listener.php
 *
 *	Copyright (c)2019 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package sms
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
ini_set('display_errors',1);

$ignoreAuth = true; // signon not required!!
$_GET['site'] = 'default';

$docroot = dirname(dirname(__FILE__));
require_once("$docroot/interface/globals.php");
require_once("$docroot/library/wmt-v3/wmt.globals.php");
require_once("$docroot/library/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Smslib;
use OpenEMR\OemrAd\Twiliolib;

$listenerType = trim(strip_tags($_REQUEST['ListenerType']));
$msgId = trim(strip_tags($_REQUEST['MessageSid']));
$fromNumber = trim(strip_tags($_REQUEST['From']));
$toNumber = trim(strip_tags($_REQUEST['To']));
$msgStatus = trim(strip_tags($_REQUEST['SmsStatus']));
$msgText = trim(strip_tags($_REQUEST['Body']));
$timestamp = trim(strip_tags($_REQUEST['message-timestamp']));

$clientRef = trim(strip_tags($_REQUEST['client-ref']));
$errorCode = trim(strip_tags($_REQUEST['err-code']));
$network = trim(strip_tags($_REQUEST['network-code']));

if (!empty($timestamp) && strtotime($timestamp) !== false) {
	$timestamp = date('Y-m-d H:i:s', strtotime($timestamp) + date('Z')); // subtract local offset from utc
}
$timestamp = (strtotime($timestamp) === false)? date('Y-m-d H:i:s') : $timestamp;
$message = urldecode($msgText); 

// Try processing SMS message
$event = '';

try {

	if (!empty($msgId) && !empty($listenerType) && $listenerType == "status_update") {
		
		// Process status update
		//$sms = new wmt\Nexmo();
		$sms = new Twiliolib();
		$sms->smsDelivery($fromNumber, $toNumber, $msgId, $timestamp, $clientRef, $msgStatus);
		
	} else if(!empty($msgId)) {
		
		// Process new message
		//$sms = new wmt\Nexmo();
		$sms = new Twiliolib();
		$sms->smsReceived($fromNumber, $toNumber, $msgId, $timestamp, $message, $msgStatus);

	}

} catch (\Exception $e) {
	error_log($e->getMessage());
}


/* DEBUG -- Determine message event

error_log( "msgId = " . $msgId );
error_log( "fromNumber = " . $fromNumber );
error_log( "toNumber = " . $toNumber );
error_log( "timestamp = " . $timestamp );
error_log( "datetime = " . $timestamp );
error_log( "errorCode = " . $errorCode );
error_log( "msgStatus = " . $msgStatus );
error_log( "message = " . $message );
error_log( "network = " . $network );

*/
?>
