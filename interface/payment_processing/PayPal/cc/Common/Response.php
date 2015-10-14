<?php
?>
<table id="apiResponse">
	<tr>
		<td>Request:</td>
	</tr>
	<tr>
		<td><textarea rows="10" cols="100"><?php echo htmlspecialchars($service->getLastRequest());?></textarea>
		</td>
	</tr>
	<tr>
		<td>Response:</td>
	</tr>
	<tr>
		<td><textarea rows="10" cols="100"><?php echo htmlspecialchars($service->getLastResponse());?></textarea>
		</td>
	</tr>
</table>
<br>
<a href="index.php">Home</a>