<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\PreapprovalDetailsRequest;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');
define("DEFAULT_SELECT", "- Select -");

/*
 * Use the PreapprovalDetails API operation to obtain information about an agreement between you and a sender for making payments on the sender’s behalf. 
 */
/*
 * The PreapprovalDetailsRequest message specifies the key of the preapproval agreement whose details you want to obtain.
 */
/*
 * (Required) Information common to each API operation, such as the language in which an error message is returned.
 */
$requestEnvelope = new RequestEnvelope("en_US");
/*
 * (Required) A preapproval key that identifies the preapproval for which you want to retrieve details. The preapproval key is returned in the PreapprovalResponse message.
 */
$preapprovalDetailsRequest = new PreapprovalDetailsRequest($requestEnvelope, $_POST['preapprovalKey']);
/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	$response = $service->PreapprovalDetails($preapprovalDetailsRequest);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Preapproval Details</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Preapproval Details</h3>
<?php 

$ack = strtoupper($response->responseEnvelope->ack);
if($ack != "SUCCESS"){
	echo "<b>Error </b>";
	echo "<pre>";
	print_r($response);
	echo "</pre>";
} else {
	echo "<pre>";
	print_r($response);
	echo "</pre>";
	echo "<table>";
	echo "<tr><td>Ack :</td><td><div id='Ack'>$ack</div> </td></tr>";
	echo "</table>";
}
require_once 'Common/Response.php';	
?>
		</div>
	</div>
</body>
</html>