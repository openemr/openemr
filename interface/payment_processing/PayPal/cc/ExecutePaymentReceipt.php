<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\ExecutePaymentRequest;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');

/*
 * The ExecutePayment API operation lets you execute a payment set up with the Pay API operation with the actionType CREATE. To pay receivers identified in the Pay call, set the pay key from the PayResponse message in the ExecutePaymentRequest message.

The ExecutePayment API operation lets you execute a payment set up with the Pay API operation with the actionType CREATE. To pay receivers identified in the Pay call, set the pay key from the PayResponse message in the ExecutePaymentRequest message. 
 */

/*
 * (Optional) The pay key that identifies the payment to be executed. This is the pay key returned in the PayResponse message. 
 */
$executePaymentRequest = new ExecutePaymentRequest(new RequestEnvelope("en_US"),$_POST['payKey']);
$executePaymentRequest->actionType = $_POST["actionType"];
/*
 * The ID of the funding plan from which to make this payment.
 */
if($_POST["fundingPlanID"] != "") {
	$executePaymentRequest->fundingPlanId = $_POST["fundingPlanID"];
}
/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	
	$response = $service->ExecutePayment($executePaymentRequest);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Execute Payment</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Execute Payment</h3>
<?php 
$ack = strtoupper($response->responseEnvelope->ack);
if($ack != "SUCCESS"){
	echo "<b>Error </b>";
	echo "<pre>";
	print_r($response);
	echo "</pre>";
} else {
	echo "<table>";
	echo "<tr><td>Ack :</td><td><div id='Ack'>$ack</div> </td></tr>";
	echo "<tr><td>PaymentExecStatus :</td><td><div id='PaymentExecStatus'>$response->paymentExecStatus</div> </td></tr>";
	echo "</table>";
	echo "<pre>";
	print_r($response);
	echo "</pre>";
}
require_once 'Common/Response.php';	
?>
		</div>
	</div>
</body>
</html>