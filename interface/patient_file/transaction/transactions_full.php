<?php
include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
?>

<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">

<a href="patient_transaction.php" target="Main" onclick="top.restoreSession()">
<font class="title"><?php xl('Patient Transactions','e'); ?></font>
<font class=more><?php echo $tback;?></font></a>

<br>
<table>

<?php

if ($result = getTransByPid($pid)) {
	
	foreach ($result as $iter) {
		if (getdate() == strtotime($iter{"date"})) {
			$date_string = "Today, " . date( "D F dS" ,strtotime($iter{"date"}));
		} else {
			$date_string = date( "D F dS" ,strtotime($iter{"date"}));
		}

    print "<tr><td><a href='transactions_full.php' target='Main' class='bold' " .
      "onclick='top.restoreSession()'>" . $date_string . " (" . $iter{"user"} .
      ")</a></td><td><a href='transactions_full.php' target='Main' " .
      "class='text' onclick='top.restoreSession()'>" . $iter{"title"} .
      "</a></td><td>" . "<a href='transactions_full.php' target='Main' " .
      "class='text' onclick='top.restoreSession()'>" .
      stripslashes($iter{"body"}) . "</a></td></tr>\n";

		$notes_count++;
		
	}
	
}
?>

</table>

</body>
</html>
