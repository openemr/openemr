<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - Refund</title>
<link rel="stylesheet" type="text/css" href="Common/sdk.css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
<script type="text/javascript" src="Common/jquery-1.3.2.min.js"></script>
</head>
<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="header">
			<h3>Refund</h3>
			<div id="apidetails">A request to make a refund based on various
				criteria. A refund can be made against the entire payKey, an
				individual transaction belonging to a payKey, a tracking id, or a
				specific receiver of a payKey.</div>
		</div>
		<div id="request_form">
			<form action="RefundReceipt.php" method="post">				
				<div class="note">A refund can be made against the entire
						payKey,or an individual transaction belonging to a payKey,or a
						tracking id, or a specific receiver of a payKey.</div>
				<div class="params">
					<div class="param_name">Pay key</div>
					<div class="param_value">
						<input name="payKey" id="payKey" value="" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Transaction Id</div>
					<div class="param_value">
						<input name="transactionId" id="transactionId" value="" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Tracking id</div>
					<div class="param_value">
						<input name="trackingId" id="trackingId" value="" />
					</div>
				</div>
				<div class="params">
					<div class="param_name">Currency code</div>
					<div class="param_value">
						<input name="currencyCode" id="currencyCode" value="USD" />
					</div>
				</div>
				<div class="section_header">Receiver info</div>
				<div class="note">Receiver is the party where funds are
						transferred to. A primary receiver receives a payment directly
						from the sender in a chained split payment. A primary receiver
						should not be specified when making a single or parallel split
						payment. Must set either mail or phone number</div>
				<table class="params" id="receiverTable">
					<tr>
						<th></th>
						<th>Email *</th>
						<th>Amount *</th>
						<th>Phone number</th>
						<th>Primary receiver</th>
						<th>Invoice Id</th>
						<th>Payment type</th>
						<th>Payment subtype</th>
					</tr>
					<tr id="receiverTable_0">
						<td align="left"><input type="checkbox" name="receiver_0"
							id="receiver_0" /></td>
						<td><input type="text" name="receiverEmail[]" id="receiveremail_0"
							value="platfo_1255612361_per@gmail.com">
						</td>
						<td><input type="text" name="receiverAmount[]" id="amount_0"
							value="1.0" class="smallfield">
						</td>
						<td style="display: block;"><input type="text"
							name="phoneCountry[]" id="phoneCountry_0" value=""
							class="xsmallfield"> <input type="text" name="phoneNumber[]"
							id="phoneNumber_0" value="" class="xsmallfield"> <input
							type="text" name="phoneExtn[]" id="phoneExtn_0" value=""
							class="xsmallfield">
						</td>
						<td><select name="primaryReceiver[]" id="primaryReceiver_0"
							class="smallfield">
								<option value="true">true</option>
								<option value="false" selected="selected">false</option>
						</select>
						</td>
						<td><input type="text" name="invoiceId[]" id="invoiceid_0"
							value="" class="smallfield">
						</td>
						<td><select name="paymentType[]" id="paymentType_0"
							class="smallfield">
								<option>- Select -</option>
								<option>GOODS</option>
								<option>SERVICE</option>
								<option>PERSONAL</option>
								<option>CASHADVANCE</option>
								<option>DIGITALGOODS</option>
						</select>
						</td>
						<td><input type="text" name="paymentSubType[]" id="paymentSubType"
							value="" class="smallfield">
						</td>
					</tr>
				</table>
				<a rel="receiverControls"></a>
				<table align="center">
					<tr>
						<td><a href="#receiverControls" onclick="cloneRow('receiverTable', 8)" id="Submit"><span>Add
									Receiver </span> </a></td>
						<td><a href="#receiverControls" onclick="deleteRow('receiverTable')" id="Submit"><span>
									Delete Receiver</span> </a></td>
					</tr>
				</table>
				<div class="submit">
					<input type="submit" value="Submit" />
				</div>
			</form>
		</div>
		<a href="index.php">Home</a>
	</div>
</body>
</html>
