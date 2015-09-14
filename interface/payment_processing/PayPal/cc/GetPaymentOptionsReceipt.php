<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\GetPaymentOptionsRequest;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');

/*
 *  You use the GetPaymentOptions API operation to retrieve the payment options passed with the SetPaymentOptionsRequest. 
 */
/*
 * (Required) Information common to each API operation, such as the language in which an error message is returned.
 */
$requestEnvelope = new RequestEnvelope("en_US");
/*
 * (Required) The pay key that identifies the payment for which you want to get payment options. This is the pay key you used to set the payment options.
 */
$getPaymentOptionsReq = new GetPaymentOptionsRequest($requestEnvelope, $_POST['payKey']);
/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	$response = $service->GetPaymentOptions($getPaymentOptionsReq);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Get Payment Options</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Get Payment Options</h3>
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
}
require_once 'Common/Response.php';	
?>
		</div>
	</div>
</body>
</html>