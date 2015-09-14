<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\Receiver;
use PayPal\Types\AP\ReceiverList;
use PayPal\Types\AP\RefundRequest;
use PayPal\Types\Common\PhoneNumberType;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');
define("DEFAULT_SELECT", "- Select -");

/*
 * 
 # Refund API
 Use the Refund API operation to refund all or part of a payment.
 This sample code uses AdaptivePayments PHP SDK to make API call
 */
/*
 * 		 `RefundRequest` which takes,
		 `Request Envelope` - Information common to each API operation, such
		 as the language in which an error message is returned.
 */
$refundRequest = new RefundRequest(new RequestEnvelope("en_US"));

// set optional params
/*
 * The receiver's email address, where n is between 0 and 5 for a maximum of 6 receivers
*/
if(isset($_POST['receiverEmail'])) {
	$receiver = array();
	for($i=0; $i<count($_POST['receiverEmail']); $i++) {
		$receiver[$i] = new Receiver();
		$receiver[$i]->email = $_POST['receiverEmail'][$i];
		$receiver[$i]->amount = $_POST['receiverAmount'][$i];
		/*
		 *  (Optional) Whether this receiver is the primary receiver, which makes this a refund for a chained payment. You can specify at most one primary receiver. Omit this field for refunds for simple and parallel payments.

Allowable values are:

    true – Primary receiver

    false – Secondary receiver (default)

		 */
		$receiver[$i]->primary = $_POST['primaryReceiver'][$i];
		if($_POST['invoiceId'][$i] != "") {
			$receiver[$i]->invoiceId = $_POST['invoiceId'][$i];
		}
		if($_POST['paymentType'][$i] != "" && $_POST['paymentType'][$i] != DEFAULT_SELECT) {
			$receiver[$i]->paymentType = $_POST['paymentType'][$i];
		}
		if($_POST['paymentSubType'][$i] != "") {
			$receiver[$i]->paymentSubType = $_POST['paymentSubType'][$i];
		}
		if($_POST['phoneCountry'][$i] != "" && $_POST['phoneNumber'][$i]) {
			$receiver[$i]->phone = new PhoneNumberType($_POST['phoneCountry'][$i], $_POST['phoneNumber'][$i]);
			if($_POST['phoneExtn'][$i] != "") {
				$receiver[$i]->phone->extension = $_POST['phoneExtn'][$i];
			}
		}
	}
	$receiverList = new ReceiverList($receiver);
}
if($_POST['currencyCode'] != "") {
	$refundRequest->currencyCode = $_POST["currencyCode"];
}
/*
 * 		 You must specify either,
		
		 * `Pay Key` - The pay key that identifies the payment for which you
		 want to retrieve details. This is the pay key returned in the
		 PayResponse message.
		 * `Transaction ID` - The PayPal transaction ID associated with the
		 payment. The IPN message associated with the payment contains the
		 transaction ID.
		 `$refundRequest->transactionId`
		 * `Tracking ID` - The tracking ID that was specified for this payment
		 in the PayRequest message.
		 `$refundRequest->trackingId`
 */
if($_POST['payKey'] != "") {
	$refundRequest->payKey = $_POST["payKey"];
}
/*
 * A PayPal transaction ID associated with the receiver whose payment you want to refund to the sender. Use field name characters exactly as shown.
 */
if($_POST['transactionId'] != "") {
	$refundRequest->transactionId = $_POST["transactionId"];
}
/*
 * The tracking ID associated with the payment that you want to refund
 */
if($_POST['trackingId'] != "") {
	$refundRequest->trackingId = $_POST["trackingId"];
}

/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	$response = $service->Refund($refundRequest);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Refund</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Refund Details</h3>
<?php
$ack = strtoupper($response->responseEnvelope->ack);
if($ack != "SUCCESS"){
	echo "<b>Error </b>";
	echo "<pre>";
	print_r($response);
	echo "</pre>";
} else { 
	$status = $response->refundInfoList->refundInfo[0]->refundStatus;
	echo "<table>";
	echo "<tr><td>Ack :</td><td><div id='Ack'>$ack</div> </td></tr>";
	echo "<tr><td>RefundStatus :</td><td><div id='RefundStatus'>$status</div></td></tr>";
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