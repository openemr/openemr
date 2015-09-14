<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Get Funding Plans</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
<script type="text/javascript" src="Common/jquery-1.3.2.min.js"></script>
</head>
<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="header">
			<h3>Get Funding Plans</h3>
			<div id="apidetails">Use the GetFundingPlans API operation to
				determine the funding sources that are available for a specified
				payment, identified by its key, which takes into account the
				preferences and country of the receiver as well as the payment
				amount. You must be both the sender of the payment and the caller of
				this API operation.</div>
		</div>
		<div id="request_form">
			<form id="Form1" name="Form1" method="post"
				action="GetFundingPlansReceipt.php">
				<div class="params">
					<div class="param_name">Pay key *</div>
					<div class="param_value">
						<input name="payKey" id="payKey" value="AP-23119815K9918782X" />
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
