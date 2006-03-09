<?
include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
?>
<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<a href="transactions_full.php" target="Main"><font class="title"><? xl('Patient Transactions','e'); ?></font><font class=more><?echo $tmore;?></font></a>

<br>
<table>

<?
//the number of transactions to display on the page at first:
$N = 6;

if ($result = getTransByPid($pid)) {
	
	$notes_count = 0;//number of notes so far displayed
	foreach ($result as $iter) {
		if ($notes_count >= $N) {
			//we have more active notes to print, but we've reached our display maximum
			print "<tr><td colspan=3 align=center><a target=Main href='transactions_full.php' class=alert>".xl('Some notes were not displayed. Click here to view all')."</a></td></tr>\n";
			break;
		}
		
		
		if (getdate() == strtotime($iter{"date"})) {
			$date_string = "Today, " . date( "D F dS" ,strtotime($iter{"date"}));
		} else {
			$date_string = date( "D F dS" ,strtotime($iter{"date"}));
		}
		
		print "<tr><td><a href='transactions_full.php' target=Main class=bold>".$date_string . " (". $iter{"user"}.")</a></td><td><a href='transactions_full.php' target=Main class=text>".$iter{"title"}."</a></td><td>" . "<a href='transactions_full.php' target=Main class=text>" . stripslashes($iter{"body"}) . "</a></td></tr>\n";
		
		
		$notes_count++;
		
	}
	
}
?>

</table>

</body>
</html>