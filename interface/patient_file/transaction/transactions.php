<?
include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
?>
<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="add_transaction.php">
<font class="title"><? xl('Patient Transactions','e'); ?></font>
<font class='more'>(Add Transaction)</font></a>
<?php } else { ?>
<a href="transactions_full.php" target="Main">
<font class="title"><? xl('Patient Transactions','e'); ?></font>
<font class=more><?echo $tmore;?></font></a>
<?php } ?>

<br>
<table>

<?
//the number of transactions to display on the page at first:
$N = $GLOBALS['concurrent_layout'] ? 9999 : 15;

if ($result = getTransByPid($pid)) {

	$notes_count = 0;//number of notes so far displayed
	foreach ($result as $iter) {
		if ($notes_count >= $N) {
			//we have more active notes to print, but we've reached our display maximum
			echo "<tr><td colspan=3 align=center><a ";
			if (!$GLOBALS['concurrent_layout']) echo "target=Main ";
			echo "href='transactions_full.php' class=alert>" .
				xl('Some notes were not displayed. Click here to view all') .
				"</a></td></tr>\n";
			break;
		}

		if (getdate() == strtotime($iter{"date"})) {
			$date_string = "Today, " . date( "D F dS" ,strtotime($iter{"date"}));
		} else {
			$date_string = date( "D F dS" ,strtotime($iter{"date"}));
		}

		if ($GLOBALS['concurrent_layout']) {
			echo "<tr><td class='bold'>" . $date_string . " (" . $iter['user'] . ")</td>";
			echo "<td class='text'>" . $iter['title'] . "</td>";
			echo "<td class='text'>" . stripslashes($iter['body']) . "</td></tr>\n";
		} else {
			echo "<tr><td><a href='transactions_full.php' ";
			echo "target=Main ";
			echo "class=bold>" . $date_string . " (" . $iter{"user"} .
				")</a></td><td><a href='transactions_full.php' ";
			echo "target=Main ";
			echo "class=text>" . $iter{"title"} . "</a></td><td>" .
				"<a href='transactions_full.php' ";
			echo "target=Main ";
			echo "class=text>" . stripslashes($iter{"body"}) . "</a></td></tr>\n";
		}

		$notes_count++;

	}
	
}
?>

</table>

</body>
</html>
