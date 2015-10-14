<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\ConfirmPreapprovalRequest;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');


// create request
$requestEnvelope = new RequestEnvelope("en_US");
$confirmPreapprovalReq = new ConfirmPreapprovalRequest($requestEnvelope, $_POST['preapprovalKey']);
// set optional params
if($_POST['fundingSourceId'] != "") {
	$confirmPreapprovalReq->fundingSourceId = $_POST['fundingSourceId'];
}
if($_POST['pin'] != "") {
	$confirmPreapprovalReq->pin = $_POST['pin'];
}
/*
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	$response = $service->ConfirmPreapproval($confirmPreapprovalReq);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Confirm Preapproval</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Confirm Preapproval</h3>
<?php
$ack = strtoupper($response->responseEnvelope->ack);
if($ack != "SUCCESS"){
	echo "<b>Error </b>";
	echo "<pre>";
	print_r($response);
	echo "</pre>";
	require_once 'Common/Response.php';
	exit;
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