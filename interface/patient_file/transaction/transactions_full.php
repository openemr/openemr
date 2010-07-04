<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

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
	 print "<tr class='showborder_head'><th style='width=10px;'>&nbsp;</th><th style='width=10px;'>&nbsp;</th><th style='width=10px;'>&nbsp;</th>";
	 print "<th style='width:140px;'>".htmlspecialchars( xl('Type'), ENT_NOQUOTES)."</th>" .
               "<th  style='width:150px;'>".htmlspecialchars( xl('Date'), ENT_NOQUOTES)."</th>" .
               "<th style='width:60px;'>".htmlspecialchars( xl('User'), ENT_NOQUOTES)."</th>" .
               "<th  style='width:180px;'>".htmlspecialchars( xl('Details'), ENT_NOQUOTES)."</th></tr>\n";
		// Print Heading .. to have better Understanding of the Listed Transactions   -- ends here

	foreach ($result as $iter) {
		if (getdate() == strtotime($iter{"date"})) {
			$date_string = "Today, " . date( "D F dS" ,strtotime($iter{"date"}));
		} else {
			$date_string = date( "D F dS" ,strtotime($iter{"date"}));
		}
		echo "<tr height='25'><td>";
		if ($iter{"title"} == "Referral") {
			//show the print button for referral forms only
                        echo "<a href='print_referral.php?transid=".
				htmlspecialchars( $iter{"id"}, ENT_NOQUOTES).
                        	"' onclick='top.restoreSession()' class='css_button_small'><span>".
                        	htmlspecialchars( xl('Print'), ENT_NOQUOTES)."</span></a>";
		}
		else {
			echo "&nbsp;";
		}
		echo "</td><td>";
		print "<a href='add_transaction.php?transid=".htmlspecialchars( $iter{"id"}, ENT_NOQUOTES).
			"&title=".htmlspecialchars( $iter{"title"}, ENT_QUOTES).
			"&inmode=edit' onclick='top.restoreSession()' class='css_button_small'><span>".
			htmlspecialchars( xl('Edit'), ENT_NOQUOTES)."</span></a>";
		echo "</td><td>";
		if (acl_check('admin', 'super')) {
			echo "<a href='../deleter.php?transaction=".
				htmlspecialchars( $iter{"id"}, ENT_QUOTES).
				"' onclick='top.restoreSession()' class='css_button_small'><span>".
				htmlspecialchars( xl('Delete'), ENT_NOQUOTES)."</span></a>";
		}
		else {
			echo "&nbsp;";
		}
		echo "</td>";
		echo "<td><b>&nbsp;" .
			generate_display_field(array('data_type'=>'1','list_id'=>'transactions'), $iter{"title"}) .
			"</b></td><td>" . htmlspecialchars( $date_string, ENT_NOQUOTES) . "</td><td>(" .
			htmlspecialchars( $iter{"user"}, ENT_NOQUOTES). ")&nbsp;</td><td>" .
			htmlspecialchars( ($iter{"body"}), ENT_NOQUOTES) . "&nbsp;</td></tr>\n";
		$notes_count++;

	}

}
?>

</table>

</body>
</html>
