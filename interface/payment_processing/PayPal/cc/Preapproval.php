<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Preapproval</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
<script type="text/javascript" src="Common/jquery-1.3.2.min.js"></script>
</head>
<?php
    $serverName = $_SERVER['SERVER_NAME'];
	$serverPort = $_SERVER['SERVER_PORT'];
	$url=dirname('http://'.$serverName.':'.$serverPort.$_SERVER['REQUEST_URI']);
	$returnUrl = $url."/Preapproval.php";
	$cancelUrl =  $url."/CancelPreapproval.php";	
?>
<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="header">
			<h3>Preapproval</h3>
			<div id="apidetails">A request to create a Preapproval. A Preapproval
				is an agreement between a Paypal account holder (the sender) and the
				API caller (the service invoker) to make payment(s) on the the
				sender's behalf with various limitations defined.</div>
		</div>
		<div id="request_form">
			<form id="Form1" name="Form1" method="post"
				action="PreapprovalReceipt.php">
				<div class="params">
					<div class="param_name">Return URL *</div>
					<div class="param_value">
						<input name="returnUrl" id="returnUrl" value="<?php echo $returnUrl;?>" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Cancel Url *</div>
					<div class="param_value">
						<input name="cancelUrl" id="cancelUrl" value="<?php echo $cancelUrl;?>" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Currency code *</div>
					<div class="param_value">
						<input name="currencyCode" value="USD" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Preapproval start date *</div>
					<div class="param_value">
						<input name="startingDate" id="startingDate" value="<?php echo date("Y-m-d");?>" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Preapproval end date</div>
					<div class="param_value">
						<input name="endingDate" id="endingDate" value="<?php echo date("Y-m-d", time() + 864000);?>" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Payment date - Date of month</div>
					<div class="param_value">
						<input name="dateOfMonth" id="dateOfMonth" value="" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Payment date - Day of week</div>
					<div class="param_value">
						<select name="dayOfWeek" id="dayOfWeek">
							<option>- Select -</option>
							<option>NO_DAY_SPECIFIED</option>
							<option>SUNDAY</option>
							<option>MONDAY</option>
							<option>TUESDAY</option>
							<option>WEDNESDAY</option>
							<option>THURSDAY</option>
							<option>FRIDAY</option>
							<option>SATURDAY</option>
						</select>
					</div>
				</div>												
				<div class="params">
					<div class="param_name">Maximum amount per payment</div>
					<div class="param_value">
						<input name="maxAmountPerPayment" id="maxAmountPerPayment" value="" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Maximum number of payments</div>
					<div class="param_value">
						<input name="maxNumberOfPayments" id="maxNumberOfPayments" value="10" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Maximum number of payments per period</div>
					<div class="param_value">
						<input name="maxNumberOfPaymentsPerPeriod" id="maxNumberOfPaymentsPerPeriod" value="" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Maximum total amount of all payments</div>
					<div class="param_value">
						<input name="maxTotalAmountOfAllPayments" id="maxTotalAmountOfAllPayments" value="50.0" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Payment period</div>
					<div class="param_value">
						<select name="paymentPeriod" id="paymentPeriod">
							<option>- Select -</option>
							<option>NO_PERIOD_SPECIFIED</option>
							<option>DAILY</option>
							<option>WEEKLY</option>
							<option>BIWEEKLY</option>
							<option>SEMIMONTHLY</option>
							<option>MONTHLY</option>
							<option>ANNUALLY</option>
						</select>
					</div>
				</div>
				<div class="params">
					<div class="param_name">Display Maximum Total Amount</div>
					<div class="param_value">
						<select name="displayMaxTotalAmount">
							<option value="">--Select a value--</option>
							<option value="true">True</option>
							<option value="false">False</option>
						</select>
					</div>
				</div>								
				<div class="params">
					<div class="param_name">Memo</div>
					<div class="param_value">
						<input name="memo" id="memo" value="" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">IPN Notification URL</div>
					<div class="param_value">
						<input name="ipnNotificationUrl" id="ipnNotificationUrl" value="" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Sender email</div>
					<div class="param_value">
						<input name="senderEmail" id="senderEmail" value="" />
					</div>
				</div>				
				<div class="params">
					<div class="param_name">Is PIN type required</div>
					<div class="param_value">
						<select name="pinType" id="pinType">
							<option>- Select -</option>
							<option>NOT_REQUIRED</option>
							<option>REQUIRED</option>
						</select>
					</div>
				</div>
				<div class="params">
					<div class="param_name">Fees payer</div>
					<div class="param_value">
						<select name="feesPayer">
							<option value="EACHRECEIVER">EACHRECEIVER</option>
							<option value="PRIMARYRECEIVER">PRIMARYRECEIVER</option>
							<option value="SENDER" selected="selected">SENDER</option>
							<option value="SECONDARYONLY">SECONDARYONLY</option>
						</select>
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