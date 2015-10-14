<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\DisplayOptions;
use PayPal\Types\AP\InitiatingEntity;
use PayPal\Types\AP\InstitutionCustomer;
use PayPal\Types\AP\InvoiceData;
use PayPal\Types\AP\InvoiceItem;
use PayPal\Types\AP\ReceiverIdentifier;
use PayPal\Types\AP\ReceiverOptions;
use PayPal\Types\AP\SenderOptions;
use PayPal\Types\AP\SetPaymentOptionsRequest;
use PayPal\Types\Common\PhoneNumberType;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');
define("DEFAULT_SELECT", "- Select -");

/*
 *  You use the SetPaymentOptions API operation to specify settings for a payment of the actionType CREATE. This actionType is specified in the PayRequest message. 
 */
$setPaymentOptionsRequest = new SetPaymentOptionsRequest(new RequestEnvelope("en_US"));
/*
 * (Required) The pay key that identifies the payment for which you want to set payment options. This is the pay key returned in the PayResponse message.
 */
$setPaymentOptionsRequest->payKey = $_POST["payKey"];

// set optional params
/*
 *  (Optional) Sender's shipping address ID.
 */
if($_POST['shippingAddressId'] != "") {
	$setPaymentOptionsRequest->shippingAddressId = $_POST['shippingAddressId'];
}

$receiverOptions = new ReceiverOptions();
$setPaymentOptionsRequest->receiverOptions[] = $receiverOptions;
if($_POST['description'] != "") {
	/*
	 * (Optional) A description you want to associate with the payment. This overrides the value of the memo in Pay API for each receiver. If this is not specified the value in the memo will be used.
	 */
	$receiverOptions->description = $_POST['description'];
}
/*
 *  (Optional) An external reference or identifier you want to associate with the payment. 
 */
if($_POST['customId'] != "") {
	$receiverOptions->customId = $_POST['customId'];
}
/*
 * 
 */
if($_POST['receiverReferrerCode'] != "") {
	$receiverOptions->referrerCode = $_POST['receiverReferrerCode'];
}
if($_POST['emailIdentifier'] != "" || ($_POST['phoneNumber'] != "" && $_POST['phoneCountry'] != "")) {
	$receiverId = new ReceiverIdentifier();	
	if($_POST['emailIdentifier'] != "") {
		$receiverId->email = $_POST['emailIdentifier'];
	}
	if($_POST['phoneNumber'] != "" && $_POST['phoneCountry'] != "") {
		$receiverId->phone = new PhoneNumberType($_POST['phoneCountry'], $_POST['phoneNumber']);
		if($_POST['phoneExtn'] != "") {
			$receiverId->phone->extension = $_POST['phoneExtn'];
		}
	}
	$receiverOptions->receiver = $receiverId;
}
$invoiceItems = array();
for($i=0; $i<count($_POST['name']); $i++) {
	if($_POST['name'][$i] != "" || $_POST['identifier'][$i] != "" || $_POST['price'][$i] != "" 
			|| $_POST['itemPrice'][$i] != "" || $_POST['itemCount'][$i] != "") {
		$item = new InvoiceItem();
		if($_POST['name'][$i] != "" ) {
			$item->name = $_POST['name'][$i]; 
		}
		/*
		 * (Optional) External reference to item or item ID. 
		 */
		if($_POST['identifier'][$i] != "" ) {
			$item->identifier = $_POST['identifier'][$i];
		}
		if($_POST['price'][$i] != "") {
			$item->price = $_POST['price'][$i];
		}
		if($_POST['itemPrice'][$i] != "") {
			$item->itemPrice = $_POST['itemPrice'][$i];
		}
		if($_POST['itemCount'][$i] != "") {
			$item->itemCount = $_POST['itemCount'][$i];
		}
		$invoiceItems[] = $item;
	}
}
if(count($invoiceItems) > 0 || $_POST['totalTax'] != "" || $_POST['totalShipping'] != "") {
	$receiverOptions->invoiceData = new InvoiceData();
	if($_POST['totalTax'] != "") {		
		$receiverOptions->invoiceData->totalTax = $_POST['totalTax'];
	}
	if($_POST['totalShipping'] != "" ) {
		$receiverOptions->invoiceData->totalShipping = $_POST['totalShipping'];
	}
	if(count($invoiceItems) > 0) {
		$receiverOptions->invoiceData->item = $invoiceItems;
	}
}
if($_POST['requireShippingAddressSelection'] != "" || $_POST['senderReferrerCode'] != "" ) {
	$setPaymentOptionsRequest->senderOptions = new SenderOptions();
	if($_POST['requireShippingAddressSelection'] != "") {
		/*
		 * (Optional) If true, require the sender to select a shipping address during the embedded payment flow; default is false. 
		 */
		$setPaymentOptionsRequest->senderOptions->requireShippingAddressSelection = $_POST['requireShippingAddressSelection'];
	}
	if($_POST['senderReferrerCode'] != "") {
		$setPaymentOptionsRequest->senderOptions->referrerCode = $_POST['senderReferrerCode'];
	}
}
if($_POST['institutionId'] != "" || $_POST['firstName'] != "" || $_POST['lastName'] != "" 
		|| $_POST['displayName'] != "" || $_POST['institutionMail'] != "" || $_POST['institutionCustomerId'] != "" 
		|| $_POST['countryCode'] != "") {
	
	$institutionCustomer = new InstitutionCustomer();
	$setPaymentOptionsRequest->initiatingEntity = new InitiatingEntity();
	$setPaymentOptionsRequest->initiatingEntity->institutionCustomer = $institutionCustomer;
/*
 *  The unique identifier assigned to the institution.

Maximum length: 64 characters
 */
	if($_POST['institutionId'] != "") {
		$institutionCustomer->institutionId = $_POST['institutionId'];
	}
	if($_POST['firstName'] != "") {
		$institutionCustomer->firstName = $_POST['firstName'];
	}
	if($_POST['lastName'] != "") {
		$institutionCustomer->lastName = $_POST['lastName'];
	}
	if($_POST['displayName'] != "") {
		$institutionCustomer->displayName = $_POST['displayName'];
	}
	if($_POST['institutionMail'] != "") {
		$institutionCustomer->email = $_POST['institutionMail'];
	}
	/*
	 * The unique identifier assigned to the consumer by the institution.

Maximum length: 64 characters
	 */
	if($_POST['institutionCustomerId'] != "") {
		$institutionCustomer->institutionCustomerId = $_POST['institutionCustomerId'];
	}
	if($_POST['countryCode'] != "") {
		$institutionCustomer->countryCode = $_POST['countryCode'];
	}
}
if($_POST['emailHeaderImageUrl'] != "" || $_POST['emailMarketingImageUrl'] != "" || $_POST['lastName'] != ""
		|| $_POST['displayName'] != "") {

	$setPaymentOptionsRequest->displayOptions = new DisplayOptions();
	if($_POST['emailHeaderImageUrl'] != "") {
		$setPaymentOptionsRequest->displayOptions->emailHeaderImageUrl = $_POST['emailHeaderImageUrl'];
	}
	if($_POST['emailMarketingImageUrl'] != "") {
		$setPaymentOptionsRequest->displayOptions->emailMarketingImageUrl = $_POST['emailMarketingImageUrl'];
	}
	if($_POST['headerImageUrl'] != "") {
		$setPaymentOptionsRequest->displayOptions->headerImageUrl = $_POST['headerImageUrl'];
	}
	if($_POST['businessName'] != "") {
		$setPaymentOptionsRequest->displayOptions->businessName = $_POST['businessName'];
	}
}

/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	$response = $service->SetPaymentOptions($setPaymentOptionsRequest);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Set Payment Options</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Set Payment Options</h3>
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