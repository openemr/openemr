<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Paypal Adaptive Payments - Webflow Common Return Page</title>
<link href="Common/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<br />
	<div id="jive-wrapper">

		<?php 
require_once 'Common/menu.html';?>
		<div id="request_form">

			<div>
				<center>
					<h3>
						<b>Paypal Adaptive Payments - Webflow Return Page</b>
					</h3>
				</center>
				<br />

				<table align="center" width="60%">
					<tr>
						<td colspan="2">
							<center>
								<h5>You have returned here from a web flow</h5>
							</center>
						</td>
					</tr>
					<?php
					foreach($_GET as $variable => $value)
					{
						echo "<tr><td>" . $variable . "</td>";
						echo "<td>" . $value . "</td></tr>";
					}
?>

				</table>
			</div>
		</div>
	</div>
</body>
</html>
