<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\GetFundingPlansRequest;
use PayPal\Types\Common\RequestEnvelope;

require_once('PPBootStrap.php');

/*
 * Use the GetFundingPlans API operation to determine the funding sources that are available for a specified payment, identified by its key, which takes into account the preferences and country of the receiver as well as the payment amount. You must be both the sender of the payment and the caller of this API operation 
 */
/*
 * The key used to create the payment whose funding sources you want to determine. 
 * The code for the language in which errors are returned, which must be en_US.
 */
$getFundingPlansReq = new GetFundingPlansRequest(new RequestEnvelope("en_US"), $_POST['payKey']);

/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	$response = $service->GetFundingPlans($getFundingPlansReq);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Get Funding Plans</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="response_form">
			<h3>Get Funding Plans</h3>
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