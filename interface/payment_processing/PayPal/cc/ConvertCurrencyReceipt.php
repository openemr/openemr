<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\ConvertCurrencyRequest;
use PayPal\Types\AP\CurrencyCodeList;
use PayPal\Types\AP\CurrencyList;
use PayPal\Types\Common\CurrencyType;
use PayPal\Types\Common\RequestEnvelope;
require_once('PPBootStrap.php');

/*
 *  # ConvertCurrency API
 Use the ConvertCurrency API operation to request the current foreign exchange (FX) rate for a specific amount and currency.
 This sample code uses AdaptivePayments PHP SDK to make API call
 */

/*
 * 	 ##ConvertCurrencyRequest
		 The ConvertCurrencyRequest message enables you to have your
		 application get an estimated exchange rate for a list of amounts.
		 This API operation does not affect PayPal balances.
 */

/*
 *  `CurrencyList` which takes two arguments:
		
		 * `CurrencyCodeType` - The currency code. Allowable values are:
		 * Australian Dollar - AUD
		 * Brazilian Real - BRL
		 `Note:
		 The Real is supported as a payment currency and currency balance only
		 for Brazilian PayPal accounts.`
		 * Canadian Dollar - CAD
		 * Czech Koruna - CZK
		 * Danish Krone - DKK
		 * Euro - EUR
		 * Hong Kong Dollar - HKD
		 * Hungarian Forint - HUF
		 * Israeli New Sheqel - ILS
		 * Japanese Yen - JPY
		 * Malaysian Ringgit - MYR
		 `Note:
		 The Ringgit is supported as a payment currency and currency balance
		 only for Malaysian PayPal accounts.`
		 * Mexican Peso - MXN
		 * Norwegian Krone - NOK
		 * New Zealand Dollar - NZD
		 * Philippine Peso - PHP
		 * Polish Zloty - PLN
		 * Pound Sterling - GBP
		 * Singapore Dollar - SGD
		 * Swedish Krona - SEK
		 * Swiss Franc - CHF
		 * Taiwan New Dollar - TWD
		 * Thai Baht - THB
		 * Turkish Lira - TRY
		 `Note:
		 The Turkish Lira is supported as a payment currency and currency
		 balance only for Turkish PayPal accounts.`
		 * U.S. Dollar - USD
		 * `amount`
 */
$baseAmountList = new CurrencyList();
foreach($_POST['currencyCode'] as $idx => $currencyCode) {
	if($_POST['currencyCode'][$idx] != "" && $_POST['currencyAmount'][$idx] != "") {
		$baseAmountList->currency[] = new CurrencyType($_POST['currencyCode'][$idx], $_POST['currencyAmount'][$idx]);
	}
}

/*
 *  `CurrencyCodeList` which contains
		
		 * `Currency Code` - Allowable values are:
		 * Australian Dollar - AUD
		 * Brazilian Real - BRL
		 `Note:
		 The Real is supported as a payment currency and currency balance only
		 for Brazilian PayPal accounts.`
		 * Canadian Dollar - CAD
		 * Czech Koruna - CZK
		 * Danish Krone - DKK
		 * Euro - EUR
		 * Hong Kong Dollar - HKD
		 * Hungarian Forint - HUF
		 * Israeli New Sheqel - ILS
		 * Japanese Yen - JPY
		 * Malaysian Ringgit - MYR
		 `Note:
		 The Ringgit is supported as a payment currency and currency balance
		 only for Malaysian PayPal accounts.`
		 * Mexican Peso - MXN
		 * Norwegian Krone - NOK
		 * New Zealand Dollar - NZD
		 * Philippine Peso - PHP
		 * Polish Zloty - PLN
		 * Pound Sterling - GBP
		 * Singapore Dollar - SGD
		 * Swedish Krona - SEK
		 * Swiss Franc - CHF
		 * Taiwan New Dollar - TWD
		 * Thai Baht - THB
		 * Turkish Lira - TRY
		 `Note:
		 The Turkish Lira is supported as a payment currency and currency
		 balance only for Turkish PayPal accounts.`
		 * U.S. Dollar - USD
 */
$convertToCurrencyList = new CurrencyCodeList();
foreach($_POST['toCurrencyCode'] as $idx => $currencyCode) {
	if($currencyCode != "") {
		$convertToCurrencyList->currencyCode[] = $currencyCode;
	}
}

/*
 * 	

		 The code for the language in which errors are returned, which must be
		 en_US.
 */

/*
 *  `ConvertCurrencyRequest` which takes params:
		
		 * `Request Envelope` - Information common to each API operation, such
		 as the language in which an error message is returned
		 * `BaseAmountList` - A list of amounts with associated currencies to
		 be converted.
		 * `ConvertToCurrencyList` - A list of currencies to convert to.
 */
$convertCurrencyReq = new ConvertCurrencyRequest(new RequestEnvelope("en_US"), $baseAmountList, $convertToCurrencyList);
if($_POST['countryCode'] != "") {
	$convertCurrencyReq->countryCode = $_POST['countryCode'];
}
if($_POST['conversionType'] != "" && $_POST['conversionType'] != "- Select -") {
	$convertCurrencyReq->conversionType = $_POST['conversionType'];
}

/*
 * 	 ## Creating service wrapper object
Creating service wrapper object to make API call and loading
Configuration::getAcctAndConfig() returns array that contains credential and config parameters
 */
$service = new AdaptivePaymentsService(Configuration::getAcctAndConfig());
try {
	/* wrap API method calls on the service object with a try catch */
	
	$response = $service->ConvertCurrency($convertCurrencyReq);
} catch(Exception $ex) {
	require_once 'Common/Error.php';
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Convert Currency</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
</head>

<body>
<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
<div id="response_form">
<h3>Convert Currency</h3>
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
?></div>
</div>
</body>
</html>