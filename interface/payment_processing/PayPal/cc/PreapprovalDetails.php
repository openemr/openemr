<?php
require_once('PPBootStrap.php');
?>
<html>
<head>
<title>PayPal Adaptive Payments - Preapproval Details</title>
<link href="Common/sdk.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="Common/sdk_functions.js"></script>
<script type="text/javascript" src="Common/jquery-1.3.2.min.js"></script>
</head>
<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<div id="header">
			<h3>Preapproval Details</h3>
			<div id="apidetails">A request to obtain information about an
				agreement between you and a sender for making payments on the
				sender’s behalf.</div>
		</div>
		<div id="request_form">
			<form method="POST" action="PreapprovalDetailsReceipt.php">
				<div class="params">
					<div class="param_name">Preapproval Key</div>
					<div class="param_value">
						<input type="text" name="preapprovalKey"
							value="PA-2T305990ET0354039" />
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