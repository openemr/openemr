<?php
function getDetailedExceptionMessage($ex) {
	if($ex instanceof PPConnectionException) {
		return "Error connecting to " . $ex->getUrl();
	} else if($ex instanceof PPConfigurationException) {
		return "Error at $ex->getLine() in $ex->getFile()";
	} else if($ex instanceof PPInvalidCredentialException || $ex instanceof PPMissingCredentialException) {
		return $ex->errorMessage();
	}
	return "";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>PayPal Adaptive Payments - SDK Exception</title>
<link href="sdk.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<div id="wrapper">
		<img src="https://devtools-paypal.com/image/bdg_payments_by_pp_2line.png"/>
		<h3>SDK Exception</h3>
		<?php if (isset($ex) && $ex instanceof Exception) {?>
		<table>
			<tr>
				<td>Type</td>
				<td><?php echo get_class($ex)?></td>
			</tr>
			<tr>
				<td>Message</td>
				<td><?php echo $ex->getMessage();?></td>
			</tr>
			<tr>
				<td>Detailed message</td>
				<td><?php echo getDetailedExceptionMessage($ex);?></td>
			</tr>
			<?php }?>
		</table>
		<br /> <a href="index.php">Home</a>
	</div>
</body>
</html>
