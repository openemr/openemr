<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\FundingConstraint;
use PayPal\Types\AP\FundingTypeInfo;
use PayPal\Types\AP\FundingTypeList;
use PayPal\Types\AP\PayRequest;
use PayPal\Types\AP\Receiver;
use PayPal\Types\AP\ReceiverList;
use PayPal\Types\AP\SenderIdentifier;
use PayPal\Types\Common\PhoneNumberType;
use PayPal\Types\Common\RequestEnvelope;

/**
 * PayReceipt.php
 * This file is called after the user clicks on a button during
 * the Pay process to use PayPal's AdaptivePayments Pay features'. The
 * user logs in to their PayPal account.
 * Called by Pay.php
 */

/*
 * Use the Pay API operation to transfer funds from a sender’s PayPal account to one or more receivers’ PayPal accounts. You can use the Pay API operation to make simple payments, chained payments, or parallel payments; these payments can be explicitly approved, preapproved, or implicitly approved.

Use the Pay API operation to transfer funds from a sender's PayPal account to one or more receivers' PayPal accounts. You can use the Pay API operation to make simple payments, chained payments, or parallel payments; these payments can be explicitly approved, preapproved, or implicitly approved. 
 */

/*
 * Create your PayRequest message by setting the common fields. If you want more than a simple payment, add fields for the specific kind of request, which include parallel payments, chained payments, implicit payments, and preapproved payments.
 */
require_once('PPBootStrap.php');
require_once('Common/Constants.php');
define("DEFAULT_SELECT", "- Select -");

if(isset($_POST['receiverEmail'])) {
	$receiver = array();
	/*
	 * A receiver's email address 
	 */
	for($i=0; $i<count($_POST['receiverEmail']); $i++) {
		$receiver[$i] = new Receiver();
		$receiver[$i]->email = $_POST['receiverEmail'][$i];
		/*
		 *  	Amount to be credited to the receiver's account 
		 */
		$receiver[$i]->amount = $_POST['receiverAmount'][$i];
		/*
		 * Set to true to indicate a chained payment; only one receiver can be a primary receiver. Omit this field, or set it to false for simple and parallel payments. 
		 */
		$receiver[$i]->primary = $_POST['primaryReceiver'][$i];

		/*
		 * (Optional) The invoice number for the payment. This data in this field shows on the Transaction Details report. Maximum length: 127 characters 
		 */
		if($_POST['invoiceId'][$i] != "") {
			$receiver[$i]->invoiceId = $_POST['invoiceId'][$i];
		}
		/*
		 * (Optional) The transaction type for the payment. Allowable values are:

    GOODS – This is a payment for non-digital goods
    SERVICE – This is a payment for services (default)
    PERSONAL – This is a person-to-person payment
    CASHADVANCE – This is a person-to-person payment for a cash advance
    DIGITALGOODS – This is a payment for digital goods
    BANK_MANAGED_WITHDRAWAL – This is a person-to-person payment for bank withdrawals, available only with special permission.

Note: Person-to-person payments are valid only for parallel payments that have the feesPayer field set to EACHRECEIVER or SENDER.
		 */
		if($_POST['paymentType'][$i] != "" && $_POST['paymentType'][$i] != DEFAULT_SELECT) {
			$receiver[$i]->paymentType = $_POST['paymentType'][$i];
		}
		/*
		 *  (Optional) The transaction subtype for the payment.
		 */
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

/*
 * The action for this request. Possible values are:

    PAY – Use this option if you are not using the Pay request in combination with ExecutePayment.
    CREATE – Use this option to set up the payment instructions with SetPaymentOptions and then execute the payment at a later time with the ExecutePayment.
    PAY_PRIMARY – For chained payments only, specify this value to delay payments to the secondary receivers; only the payment to the primary receiver is processed.

 */
/*
 * The code for the currency in which the payment is made; you can specify only one currency, regardless of the number of receivers 
 */
/*
 * URL to redirect the sender's browser to after canceling the approval for a payment; it is always required but only used for payments that require approval (explicit payments) 
 */
/*
 * URL to redirect the sender's browser to after the sender has logged into PayPal and approved a payment; it is always required but only used if a payment requires explicit approval 
 */
$payRequest = new PayRequest(new RequestEnvelope("en_US"), $_POST['actionType'], $_POST['cancelUrl'], $_POST['currencyCode'], $receiverList, $_POST['returnUrl']);
// Add optional params
/*
 *  (Optional) The payer of PayPal fees. Allowable values are:

    SENDER – Sender pays all fees (for personal, implicit simple/parallel payments; do not use for chained or unilateral payments)
    PRIMARYRECEIVER – Primary receiver pays all fees (chained payments only)
    EACHRECEIVER – Each receiver pays their own fee (default, personal and unilateral payments)
    SECONDARYONLY – Secondary receivers pay all fees (use only for chained payments with one secondary receiver)

 */
if($_POST["feesPayer"] != "") {
	$payRequest->feesPayer = $_POST["feesPayer"];
}
/*
 *  (Optional) The key associated with a preapproval for this payment. The preapproval key is required if this is a preapproved payment.
Note: The Preapproval API is unavailable to API callers with Standard permission levels.
 */
if($_POST["preapprovalKey"] != "") {
	$payRequest->preapprovalKey  = $_POST["preapprovalKey"];
}
/*
 * (Optional) The URL to which you want all IPN messages for this payment to be sent. Maximum length: 1024 characters 
 */
if($_POST['ipnNotificationUrl'] != "") {
	$payRequest->ipnNotificationUrl = $_POST['ipnNotificationUrl'];
}
if($_POST["memo"] != "") {
	$payRequest->memo = $_POST["memo"];
}
/*
 * (Optional) The sender's personal identification number, which was specified when the sender signed up for a preapproval. 
 */
if($_POST["pin"] != "") {
	$payRequest->pin  = $_POST["pin"];
}
if($_POST['preapprovalKey'] != "") {
	$payRequest->preapprovalKey  = $_POST["preapprovalKey"];
}
if($_POST['reverseAllParallelPaymentsOnError'] != "") {
	$payRequest->reverseAllParallelPaymentsOnError  = $_POST["reverseAllParallelPaymentsOnError"];
}
if($_POST['senderEmail'] != "") {
	$payRequest->senderEmail  = $_POST["senderEmail"];
}
/*
 *(Optional) A unique ID that you specify to track the payment.
Note: You are responsible for ensuring that the ID is unique. 
 */
if($_POST['trackingId'] != "") {
	$payRequest->trackingId  = $_POST["trackingId"];
}
/*
 * 
 */
if($_POST['fundingConstraint'] != "" && $_POST['fundingConstraint'] != DEFAULT_SELECT) {
	$payRequest->fundingConstraint = new FundingConstraint();
	/*
	 * Specifies a list of allowed funding selections for the payment. This is a list of funding selections that can be combined in any order to allow payments to use the indicated funding type. If this field is omitted, the payment can be funded by any funding type that is supported for Adaptive Payments. Allowable values are:

    ECHECK – Electronic check
    BALANCE – PayPal account balance
    CREDITCARD – Credit card

Note: ECHECK and CREDITCARD include BALANCE implicitly.
Note: FundingConstraint is unavailable to API callers with standard permission levels; for more information, refer to the section Adaptive Payments Permission Levels.
	 */
	$payRequest->fundingConstraint->allowedFundingType = new FundingTypeList();
	$payRequest->fundingConstraint->allowedFundingType->fundingTypeInfo = array();
	$payRequest->fundingConstraint->allowedFundingType->fundingTypeInfo[]  = new FundingTypeInfo($_POST["fundingConstraint"]);
}
/*
 *(Optional) If true, use credentials to identify the sender; default is false. 
 */
if($_POST['emailIdentifier'] != "" || $_POST['senderCountryCode'] != "" || $_POST['senderPhoneNumber'] != "" 
		|| $_POST['senderExtension'] != "" || $_POST['useCredentials'] != "" ) {
	$payRequest->sender = new SenderIdentifier();
	if($_POST['emailIdentifier'] != "") {
		$payRequest->sender->email  = $_POST["emailIdentifier"];
	}
	if($_POST['senderCountryCode'] != "" || $_POST['senderPhoneNumber'] != "" || $_POST['senderExtension'] != "") {
		$payRequest->sender->phone = new PhoneNumberType();
		if($_POST['senderCountryCode'] != "") {
			$payRequest->sender->phone->countryCode  = $_POST["senderCountryCode"];
		}
		if($_POST['senderPhoneNumber'] != "") {
			$payRequest->sender->phone->phoneNumber  = $_POST["senderPhoneNumber"];
		}
		if($_POST['senderExtension'] != "") {
			$payRequest->sender->phone->extension  = $_POST["senderExtension"];
		}
	}
	if($_POST['useCredentials'] != "") {
		$payRequest->sender->useCredentials  = $_POST["useCredentials"];
	}
}
/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
configuration file for your credentials and endpoint
*/
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	$response = $service->Pay($payRequest);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;
}
/* Make the call to PayPal to get the Pay token
 If the API call succeded, then redirect the buyer to PayPal
to begin to authorize payment.  If an error occured, show the
resulting errors */


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
<title>PayPal Adaptive Payments - Pay Response</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/tooltip.js">
    </script>
</head>

<body>	
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Pay - Response</h3>			
<?php
$ack = strtoupper($response->responseEnvelope->ack);
if($ack != "SUCCESS") {
	echo "<b>Error </b>";
	echo "<pre>";
	echo "</pre>";
} else {
	$payKey = $response->payKey;
	if(($response->paymentExecStatus == "COMPLETED" )) {
		$case ="1";
	} else if(($_POST['actionType']== "PAY") && ($response->paymentExecStatus == "CREATED" )) {
		$case ="2";
	} else if(($_POST['preapprovalKey'] != null ) && ($_POST['actionType'] == "CREATE") && ($response->paymentExecStatus == "CREATED" )) {
		$case ="3";
	} else if(($_POST['actionType']== "PAY_PRIMARY")) {
		$case ="4";
	} else if(($_POST['actionType']== "CREATE") && ($response->paymentExecStatus == "CREATED" )) {
// check if API caller is the money sender (implicit payment)
		if('jb-us-seller@paypal.com' == $_POST["senderEmail"]) {
			$case ="3";
		} else {
			$case ="2";
		}
	}
	$token = $response->payKey;
	$payPalURL = PAYPAL_REDIRECT_URL . '_ap-payment&paykey=' . $token;
	switch($case) {
		case "1" :
			echo "<table>";
			echo "<tr><td>Ack :</td><td><div id='Ack'>$ack</div> </td></tr>";
			echo "<tr><td>PayKey :</td><td><div id='PayKey'>$payKey</div> </td></tr>";
			echo "</table>";
			break;
		case "2" :
			echo "<table>";
			echo "<tr><td>Ack :</td><td><div id='Ack'>$ack</div> </td></tr>";
			echo "<tr><td>PayKey :</td><td><div id='PayKey'>$payKey</div> </td></tr>";
			echo "<tr><td><a href=$payPalURL><b>Redirect URL to Complete Payment </b></a></td></tr>";
			echo "</table>";
			break;
		case "3" :
			echo "<table>";
			echo "<tr><td>Ack :</td><td><div id='Ack'>$ack</div> </td></tr>";
			echo "<tr><td>PayKey :</td><td><div id='PayKey'>$payKey</div> </td></tr>";
			echo "<tr><td><a href=$payPalURL><b>Redirect URL to Complete Payment </b></a></td></tr>";
			echo "<tr><td><a href=SetPaymentOptions.php?payKey=$payKey><b>Set Payment Options(optional)</b></a></td></tr>";
			echo "<tr><td><a href=ExecutePayment.php?payKey=$payKey><b>Execute Payment </b></a></td></tr>";
			echo "</table>";
			break;
		case "4" :
			echo"Payment to \"Primary Receiver\" is Complete<br/>";
			echo"<a href=ExecutePayment.php?payKey=$payKey><b>* \"Execute Payment\" to pay to the secondary receivers</b></a><br>";
			break;
	}
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
