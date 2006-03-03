<?
include_once("../../globals.php");
include_once("$srcdir/onotes.inc");




//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
$N = 10;
?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">


</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>



<a href="office_comments_full.php" target="Main"><font class="title"><? xl('Office Notes','e'); ?></font><font class=more><?echo $tmore;?></font></a>

<br>


<table border=0 width=100%>

<?


//retrieve all active notes
if($result = getOnoteByDate("", 1, "date,body,user","all",0)) {

$notes_count = 0;//number of notes so far displayed
foreach ($result as $iter) {
	if ($notes_count >= $N) {
		//we have more active notes to print, but we've reached our display maximum
		print "<tr><td colspan=3 align=center><a target=Main href='office_comments_full.php?active=1' class=alert>Some notes were not displayed. Click here to view all</a></td></tr>\n";
		break;
	}
	
	
	if (getdate() == strtotime($iter{"date"})) {
		$date_string = "Today, " . date( "D F dS" ,strtotime($iter{"date"}));
	} else {
		$date_string = date( "D F dS" ,strtotime($iter{"date"}));
	}
	
	print "<tr><td width=20% valign=top><font class=bold>".$date_string . "</font> <font class=bold>(". $iter{"user"}.")</font><br>" . "<font class=text>" . stripslashes($iter{"body"}) . "</font></td></tr>\n";
	
	
	$notes_count++;
}

}
?>

</table>



</body>
</html>
