<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Convert Currency</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
<script type="text/javascript" src="Common/jquery-1.3.2.min.js"></script>
</head>
<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="header">
			<h3>Convert Currency</h3>
			<div id="apidetails">The ConvertCurrency API operation is used to
				obtain the estimated current foreign exchange (FX) rate for a
				specific amount and currency. This is an estimated rate, which may
				be different from the FX rate used at the time of the transaction.</div>
		</div>
		<div id="request_form">
			<form id="Form1" name="Form1" method="post"
				action="ConvertCurrencyReceipt.php">
				<div class="input_header">Base Amount</div>
				<table class="params">
					<tr>
						<th>Code *</th>
						<th>Amount *</th>
					</tr>
					<tr>
						<td><input type="text" name="currencyCode[]" id="currencyCode_0" value="EUR" /></td>
						<td><input type="text" name="currencyAmount[]" id="currencyAmount_0" value="10.0" /></td>
					</tr>
					<tr>
						<td><input type="text" name="currencyCode[]" id="currencyCode_1" value="GBP" /></td>
						<td><input type="text" name="currencyAmount[]" id="currencyAmount_1" value="15.0" /></td>
					</tr>					
				</table>
				<div class="input_header">Convert to currency</div>
				<table class="params">
					<tr>
						<th>Code *</th>						
					</tr>
					<tr>
						<td><input type="text" name="toCurrencyCode[]" id="toCurrencyCode_0" value="USD" /></td>						
					</tr>
					<tr>
						<td><input type="text" name="toCurrencyCode[]" id="toCurrencyCode_1" value="SGD" /></td>						
					</tr>
				</table>			
				<div class="params">
					<div class="param_name">Conversion type</div>
					<div class="param_value">
						<select name="conversionType" id="conversionType">
							<option>- Select -</option>
							<option>SENDER_SIDE</option>
							<option>RECEIVER_SIDE</option>
							<option>BALANCE_TRANSFER</option>
						</select>
					</div>
				</div>
				<div class="params">
					<div class="param_name">Country code (2 character ISO code for the country where the function is suppposed to happen)</div>
					<div class="param_value">
						<input name="countryCode" id="countryCode"
							value="" />
					</div>
				</div>				
				<div class="submit">
					<input type="submit" value="Submit" />
				</div>
			</form>
		</div>
		<a href="index.php">Home</a>
	</div>
</body>
</html>
