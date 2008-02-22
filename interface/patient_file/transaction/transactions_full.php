<?php
include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
?>

<html>
<head>
<? html_header_show();?>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<a href="patient_transaction.php" target="Main" onclick="top.restoreSession()">
<font class="title"><? xl('Patient Transactions','e'); ?></font>
<font class=more><?echo $tback;?></font></a>

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
