<?php
require_once("../globals.php");
?>
<h1>Rx Log</h1>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<?php

$log = sqlStatement("SELECT * FROM prescription_rx_log ORDER BY id DESC");

print "<table width='100%'>";
print "<tr align='left'>

<th>Rx ID</th>
<th>Date</th>
<th>Time</th>
<th>Code</th>
<th>Status</th>
<th>Message</th>
</tr>";
while($row = sqlFetchArray($log)){

	print "<tr><td>" .$row['prescription_id']."</td><td>".$row['date'].
          "</td><td>".$row['time']."</td><td>".$row['code']."</td><td>".$row['status'].
          "</td><td>".$row['message_id']."</td></tr>";		  
	
	}
print "</table>";
?>
</body>
</html>