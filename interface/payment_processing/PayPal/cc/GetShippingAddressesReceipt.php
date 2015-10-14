<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\GetShippingAddressesRequest;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');

/*
 * Use the GetShippingAddresses API operation to obtain the selected shipping address. You must have created the payment or preapproval key that identifies the account holder whose shipping address you want to obtain, or be the primary receiver of the payment or one of the parallel receivers of the payment. The shipping address is available only if it was provided during the embedded payment flow. 
 */
/*
 *  (Required) The payment paykey that identifies the account holder for whom you want to obtain the selected shipping address.
Note:

Shipping information can only be retrieved through the payment payKey; not through the preapprovalKey.

 */
/*
 * (Required) Information common to each API operation, such as the language in which an error message is returned.
 */
$getShippingAddressesReq = new GetShippingAddressesRequest(new RequestEnvelope("en_US"), $_POST['key']);
/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	$response = $service->GetShippingAddresses($getShippingAddressesReq);
} catch(Exception $ex) {
	require_once 'Comon/Error.php';
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Get Shipping Addresses</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Get Shipping Addresses</h3>
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