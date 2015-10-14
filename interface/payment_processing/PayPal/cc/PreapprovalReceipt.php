<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\PreapprovalRequest;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');
define("DEFAULT_SELECT", "- Select -");

/*
 *  # Preapproval API
 Use the Preapproval API operation to set up an agreement between yourself
 and a sender for making payments on the sender's behalf. You set up a
 preapprovals for a specific maximum amount over a specific period of time
 and, optionally, by any of the following constraints:

 * the number of payments
 * a maximum per-payment amount
 * a specific day of the week or the month
 * whether or not a PIN is required for each payment request.
 This sample code uses AdaptivePayments PHP SDK to make API call
 */

/*
 * The code for the language in which errors are returned, which must be en_US. 
 */
$requestEnvelope = new RequestEnvelope("en_US");
/*
 * URL to redirect the sender's browser to after canceling the preapproval 
 */
/*
 * URL to redirect the sender's browser to after the sender has logged into PayPal and confirmed the preapproval 
 */
/*
 * The code for the currency in which the payment is made; you can specify only one currency, regardless of the number of receivers 
 */
/*
 * First date for which the preapproval is valid. It cannot be before today's date or after the ending date. 
 */
$preapprovalRequest = new PreapprovalRequest($requestEnvelope, $_POST['cancelUrl'], 
				$_POST['currencyCode'], $_POST['returnUrl'], $_POST['startingDate']);
// Set optional params
/*
 * (Optional) The day of the month on which a monthly payment is to be made. Allowable values are numbers between 0 and 31. A number between 1 and 31 indicates the date of the month. Specifying 0 indicates that payment can be made on any day of the month. 
 */
if($_POST['dateOfMonth'] != null) {
	$preapprovalRequest->dateOfMonth = $_POST['dateOfMonth'];
}
/*
 *  (Optional) The day of the week that a weekly payment is to be made. Allowable values are:

    NO_DAY_SPECIFIED
    SUNDAY
    MONDAY
    TUESDAY
    WEDNESDAY
    THURSDAY
    FRIDAY
    SATURDAY

 */
if($_POST['dayOfWeek'] != null && $_POST['dayOfWeek'] != DEFAULT_SELECT) {
	$preapprovalRequest->dayOfWeek = $_POST['dayOfWeek'];
}
if($_POST['dateOfMonth'] != null) {
	$preapprovalRequest->dateOfMonth = $_POST['dateOfMonth'];
}
/*
 * (Optional) Last date for which the preapproval is valid. It cannot be later than one year from the starting date.
Note: You must specify a value unless you have specific permission from PayPal to omit this value. 
 */
if($_POST['endingDate'] != null) {
	$preapprovalRequest->endingDate = $_POST['endingDate'];
}
/*
 * (Optional) The preapproved maximum amount per payment. It cannot exceed the preapproved maximum total amount of all payments. 
 */
if($_POST['maxAmountPerPayment'] != null) {
	$preapprovalRequest->maxAmountPerPayment = $_POST['maxAmountPerPayment'];
}
/*
 * (Optional) The preapproved maximum number of payments. It cannot exceed the preapproved maximum total number of all payments. 
 */
if($_POST['maxNumberOfPayments'] != null) {
	$preapprovalRequest->maxNumberOfPayments = $_POST['maxNumberOfPayments'];
}
/*
 * (Optional) The preapproved maximum number of all payments per period. You must specify a value unless you have specific permission from PayPal.  
 */
if($_POST['maxNumberOfPaymentsPerPeriod'] != null) {
	$preapprovalRequest->maxNumberOfPaymentsPerPeriod = $_POST['maxNumberOfPaymentsPerPeriod'];
}
/*
 * (Optional) The preapproved maximum total amount of all payments. It cannot exceed $2,000 USD or its equivalent in other currencies.
Note: You must specify a value unless you have specific permission from PayPal to omit this value. 
 */
if($_POST['maxTotalAmountOfAllPayments'] != null) {
	$preapprovalRequest->maxTotalAmountOfAllPayments = $_POST['maxTotalAmountOfAllPayments'];
}
/*
 *  (Optional) The payment period. It is one of the following values:

    NO_PERIOD_SPECIFIED
    DAILY – Each day
    WEEKLY – Each week
    BIWEEKLY – Every other week
    SEMIMONTHLY – Twice a month
    MONTHLY – Each month
    ANNUALLY – Each year

 */
if($_POST['paymentPeriod'] != null && $_POST['paymentPeriod'] != DEFAULT_SELECT) {
	$preapprovalRequest->paymentPeriod = $_POST['paymentPeriod'];
}
/*
 *  (Optional) A note about the preapproval. Maximum length: 1000 characters, including newline characters 
 */
if($_POST['memo'] != null) {
	$preapprovalRequest->memo = $_POST['memo'];
}
/*
 *  (Optional) The URL to which you want all IPN messages for this preapproval to be sent. This URL supersedes the IPN notification URL in your profile. Maximum length: 1024 characters 
 */
if($_POST['ipnNotificationUrl'] != null) {
	$preapprovalRequest->ipnNotificationUrl = $_POST['ipnNotificationUrl'];
}
/*
 * (Optional) Sender's email address. If not specified, the email address of the sender who logs in to approve the request becomes the email address associated with the preapproval key. Maximum length: 127 characters 
 */
if($_POST['senderEmail'] != null) {
	$preapprovalRequest->senderEmail = $_POST['senderEmail'];
}
/*
 * (Optional) Whether a personal identification number (PIN) is required. It is one of the following values:

    NOT_REQUIRED – A PIN is not required (default)
    REQUIRED – A PIN is required; the sender must specify a PIN when setting up the preapproval on PayPal

 */
if($_POST['pinType'] != null && $_POST['pinType'] != DEFAULT_SELECT) {
	$preapprovalRequest->pinType = $_POST['pinType'];
}
/*
 * (Optional) The payer of PayPal fees. Allowable values are:

    SENDER – Sender pays all fees (for personal, implicit simple/parallel payments; do not use for chained or unilateral payments)
    PRIMARYRECEIVER – Primary receiver pays all fees (chained payments only)
    EACHRECEIVER – Each receiver pays their own fee (default, personal and unilateral payments)
    SECONDARYONLY – Secondary receivers pay all fees (use only for chained payments with one secondary receiver)

 */
if($_POST['feesPayer'] != null) {
	$preapprovalRequest->feesPayer = $_POST['feesPayer'];
}
/*
 * (Optional) Whether to display the maximum total amount of this preapproval. It is one of the following values:

    TRUE – Display the amount
    FALSE – Do not display the amount (default)

 */
if($_POST['displayMaxTotalAmount'] != null) {
	$preapprovalRequest->displayMaxTotalAmount = $_POST['displayMaxTotalAmount'];
}
/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	$response = $service->Preapproval($preapprovalRequest);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Preapproval</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Preapproval</h3>
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
	
	// Redirect to paypal.com here
	$token = $response->preapprovalKey;
	$payPalURL = 'https://www.sandbox.paypal.com/webscr&cmd=_ap-preapproval&preapprovalkey='.$token;
	
	echo "<table>";
	echo "<tr><td>Ack :</td><td><div id='Ack'>$ack</div> </td></tr>";
	echo "<tr><td>PreapprovalKey :</td><td><div id='PreapprovalKey'>$token</div> </td></tr>";
	echo "<tr><td><a href=$payPalURL><b>Redirect URL to Complete Preapproval Authorization</b></a></td></tr>";
	echo "</table>";
}
require_once 'Common/Response.php';		
?>
		</div>
	</div>
</body>
</html>