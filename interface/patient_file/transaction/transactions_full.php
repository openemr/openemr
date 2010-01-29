<?php
include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
require_once("$srcdir/options.inc.php");
?>

<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script language="javascript">
// Called by the deleteme.php window on a successful delete.
function imdeleted() {
 top.restoreSession();
 location.href = 'transaction/transactions.php';
}
</script>
</head>
<body class="body_top">

<br>
<table class="showborder" cellspacing="0px" cellpadding="2px">

<?php

if ($result = getTransByPid($pid)) {

	// Print Heading .. to have better Understanding of the Listed Transactions -- starts here Dec 07,09
	 print "<tr class='showborder_head'><th style='width=10px;'>&nbsp;</th>";
	 print "<th style='width:140px;'>Type</th><th  style='width:150px;'>Date</th><th style='width:60px;'>User</th><th  style='width:180px;'>Details</th></tr>\n";
		// Print Heading .. to have better Understanding of the Listed Transactions   -- ends here

	foreach ($result as $iter) {
		if (getdate() == strtotime($iter{"date"})) {
			$date_string = "Today, " . date( "D F dS" ,strtotime($iter{"date"}));
		} else {
			$date_string = date( "D F dS" ,strtotime($iter{"date"}));
		}
	    print "<tr height='25'>
		<td><a href='add_transaction.php?transid=".$iter{"id"}.
      "&title=".$iter{"title"}."&inmode=edit' onclick='top.restoreSession()' class='css_button_small'><span>".xl('Edit')."</span></a>";
	  if (acl_check('admin', 'super'))  echo "<a href='../deleter.php?transaction=".$iter{"id"}."' onclick='top.restoreSession()' class='css_button_small'><span>".xl('Delete')."</span></a></td>";
		echo "<td>" .$iter{"title"} ."&nbsp;</td><td>".$date_string .
      "</td><td>(" . $iter{"user"}.
      ")&nbsp;</td><td>" .  stripslashes($iter{"body"}) . "&nbsp;</td></tr>\n";
		$notes_count++;

	}

}
?>

</table>

</body>
</html>
