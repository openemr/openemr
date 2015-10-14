<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\PaymentDetailsRequest;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');
/*
 *  # PaymentDetails API
 Use the PaymentDetails API operation to obtain information about a payment. You can identify the payment by your tracking ID, the PayPal transaction ID in an IPN message, or the pay key associated with the payment.
 This sample code uses AdaptivePayments PHP SDK to make API call
 */
/*
 * 
		 PaymentDetailsRequest which takes,
		 `Request Envelope` - Information common to each API operation, such
		 as the language in which an error message is returned.
 */
$requestEnvelope = new RequestEnvelope("en_US");
/*
 * 		 PaymentDetailsRequest which takes,
		 `Request Envelope` - Information common to each API operation, such
		 as the language in which an error message is returned.
 */
$paymentDetailsReq = new PaymentDetailsRequest($requestEnvelope);
/*
 * 		 You must specify either,
		
		 * `Pay Key` - The pay key that identifies the payment for which you want to retrieve details. This is the pay key returned in the PayResponse message.
		 * `Transaction ID` - The PayPal transaction ID associated with the payment. The IPN message associated with the payment contains the transaction ID.
		 `paymentDetailsRequest.setTransactionId(transactionId)`
		 * `Tracking ID` - The tracking ID that was specified for this payment in the PayRequest message.
		 `paymentDetailsRequest.setTrackingId(trackingId)`
 */
if($_POST['payKey'] != "") {
	$paymentDetailsReq->payKey = $_POST['payKey'];
}
if($_POST['transactionId'] != "") {
	$paymentDetailsReq->transactionId = $_POST['transactionId'];
}
if($_POST['trackingId'] != "") {
	$paymentDetailsReq->trackingId = $_POST['trackingId'];
}

/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	$response = $service->PaymentDetails($paymentDetailsReq);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Payment Details</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Payment Details</h3>
<?php 
$ack = strtoupper($response->responseEnvelope->ack);
if($ack != "SUCCESS"){
	echo "<b>Error </b>";
	echo "<pre>";
	print_r($response);
	echo "</pre>";
} else {
/*
 * 			 The status of the payment. Possible values are:
			
			 * CREATED - The payment request was received; funds will be
			 transferred once the payment is approved
			 * COMPLETED - The payment was successful
			 * INCOMPLETE - Some transfers succeeded and some failed for a
			 parallel payment or, for a delayed chained payment, secondary
			 receivers have not been paid
			 * ERROR - The payment failed and all attempted transfers failed
			 or all completed transfers were successfully reversed
			 * REVERSALERROR - One or more transfers failed when attempting
			 to reverse a payment
			 * PROCESSING - The payment is in progress
			 * PENDING - The payment is awaiting processing
 */
	echo "<table>";
	echo "<tr><td>Ack :</td><td><div id='Ack'>$ack</div> </td></tr>";
	echo "<tr><td>PayKey :</td><td><div id='PayKey'>$response->payKey</div> </td></tr>";
	echo "<tr><td>Status :</td><td><div id='Status'>$response->status</div> </td></tr>";
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