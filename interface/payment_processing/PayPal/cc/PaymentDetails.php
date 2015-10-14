<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Payment Details</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
<script type="text/javascript" src="Common/jquery-1.3.2.min.js"></script>
</head>
<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="header">
			<h3>Payment Details</h3>
			<div id="apidetails">The request to look up the details of a
				PayRequest. The PaymentDetailsRequest can be made with either a
				payKey, trackingId, or a transactionId of the PayRequest.</div>
		</div>
		<div id="request_form">
			<form id="Form1" name="Form1" method="post"
				action="PaymentDetailsReceipt.php">
				<div class="note">Only one is required to identify the
						payment, if more than one is specified, all fields must identify
						the same payment.</div>
				<div class="params">
					<div class="param_name">Pay key</div>
					<div class="param_value">
						<input name="payKey" id="payKey" value="AP-5S482348KH512131U" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Transaction Id</div>
					<div class="param_value">
						<input name="transactionId" id="transactionId" value="" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Tracking Id</div>
					<div class="param_value">
						<input name="trackingId" id="trackingId" value="" />
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
